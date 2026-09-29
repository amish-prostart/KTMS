/*
 * KTMS live match client.
 *
 * Shared by the scoring console, the timer console and the broadcast display.
 *
 * The server owns the truth. It hands back a full snapshot after every action
 * and on every poll. Between polls the clocks are interpolated locally from the
 * moment the snapshot arrived, which keeps the countdown smooth at 60fps without
 * ever letting the browser become the authority on the time remaining.
 */
(function (window, document) {
    'use strict';

    function csrfToken() {
        const meta = document.querySelector('meta[name="csrf-token"]');

        return meta ? meta.getAttribute('content') : '';
    }

    function formatClock(totalSeconds) {
        const seconds = Math.max(0, Math.floor(totalSeconds));
        const minutes = Math.floor(seconds / 60);

        return String(minutes).padStart(2, '0') + ':' + String(seconds % 60).padStart(2, '0');
    }

    /**
     * A countdown anchored to the instant the server value was received.
     */
    function LiveClock() {
        this.remaining = 0;
        this.running = false;
        this.duration = 0;
        this.anchor = (window.performance || Date).now();
    }

    LiveClock.prototype.sync = function (payload) {
        if (!payload) {
            return;
        }

        this.remaining = Number(payload.remaining) || 0;
        this.running = Boolean(payload.running);
        this.duration = Number(payload.duration) || 0;
        this.anchor = (window.performance || Date).now();
    };

    LiveClock.prototype.value = function () {
        if (!this.running) {
            return this.remaining;
        }

        const elapsed = ((window.performance || Date).now() - this.anchor) / 1000;

        return Math.max(0, this.remaining - elapsed);
    };

    LiveClock.prototype.formatted = function () {
        return formatClock(this.value());
    };

    /**
     * Fraction of the clock still to run, 0..1. Used for the raid clock ring.
     */
    LiveClock.prototype.fraction = function () {
        if (!this.duration) {
            return 0;
        }

        return Math.max(0, Math.min(1, this.value() / this.duration));
    };

    function LiveSession(options) {
        this.stateUrl = options.stateUrl;
        this.pollInterval = options.pollInterval || 2000;
        this.gameClock = new LiveClock();
        this.raidClock = new LiveClock();
        this.state = options.initialState || null;
        this.stateHandlers = [];
        this.tickHandlers = [];
        this.pending = 0;

        if (this.state) {
            this.syncClocks(this.state);
        }
    }

    LiveSession.prototype.onState = function (handler) {
        this.stateHandlers.push(handler);

        return this;
    };

    LiveSession.prototype.onTick = function (handler) {
        this.tickHandlers.push(handler);

        return this;
    };

    LiveSession.prototype.syncClocks = function (state) {
        if (state && state.clocks) {
            this.gameClock.sync(state.clocks.game);
            this.raidClock.sync(state.clocks.raid);
        }
    };

    LiveSession.prototype.apply = function (state) {
        if (!state) {
            return;
        }

        this.state = state;
        this.syncClocks(state);

        this.stateHandlers.forEach((handler) => {
            try {
                handler(state, this);
            } catch (error) {
                console.error('KTMS state handler failed', error);
            }
        });
    };

    LiveSession.prototype.start = function () {
        const self = this;

        // Render loop: smooth clocks, independent of network activity.
        function frame() {
            self.tickHandlers.forEach((handler) => {
                try {
                    handler(self);
                } catch (error) {
                    console.error('KTMS tick handler failed', error);
                }
            });

            window.requestAnimationFrame(frame);
        }

        window.requestAnimationFrame(frame);

        // Poll loop: pull the authoritative snapshot.
        this.refresh();
        window.setInterval(function () {
            // Skip polling while the tab is hidden; we refresh on focus instead.
            if (document.hidden) {
                return;
            }

            self.refresh();
        }, this.pollInterval);

        document.addEventListener('visibilitychange', function () {
            if (!document.hidden) {
                self.refresh();
            }
        });

        return this;
    };

    LiveSession.prototype.refresh = function () {
        const self = this;

        return window.fetch(this.stateUrl, {
            headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            credentials: 'same-origin',
        })
            .then((response) => (response.ok ? response.json() : Promise.reject(response)))
            .then((payload) => {
                self.setConnected(true);
                self.apply(payload.state);

                return payload.state;
            })
            .catch(() => {
                self.setConnected(false);
            });
    };

    /**
     * Send an operator action and apply the snapshot that comes back.
     */
    LiveSession.prototype.act = function (url, body) {
        const self = this;
        this.pending += 1;
        this.setBusy(true);

        return window.fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                Accept: 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': csrfToken(),
            },
            credentials: 'same-origin',
            body: JSON.stringify(body || {}),
        })
            .then(function (response) {
                return response.json()
                    .catch(() => ({}))
                    .then((payload) => ({ ok: response.ok, status: response.status, payload }));
            })
            .then(function (result) {
                if (!result.ok) {
                    self.reportValidation(result.payload, result.status);

                    return null;
                }

                self.setConnected(true);
                self.apply(result.payload.state);

                (result.payload.messages || []).forEach((message) => self.toast(message, 'warning'));

                return result.payload.state;
            })
            .catch(function () {
                self.setConnected(false);
                self.toast('Could not reach the server. The action was not saved.', 'danger');

                return null;
            })
            .finally(function () {
                self.pending -= 1;

                if (self.pending <= 0) {
                    self.pending = 0;
                    self.setBusy(false);
                }
            });
    };

    LiveSession.prototype.reportValidation = function (payload, status) {
        if (payload && payload.errors) {
            Object.keys(payload.errors).forEach((field) => {
                (payload.errors[field] || []).forEach((message) => this.toast(message, 'danger'));
            });

            return;
        }

        this.toast((payload && payload.message) || 'That action was rejected (' + status + ').', 'danger');
    };

    LiveSession.prototype.setBusy = function (busy) {
        document.body.classList.toggle('ktms-busy', Boolean(busy));
    };

    LiveSession.prototype.setConnected = function (connected) {
        const indicator = document.querySelector('[data-connection]');

        if (!indicator) {
            return;
        }

        indicator.classList.toggle('text-success', connected);
        indicator.classList.toggle('text-danger', !connected);
        indicator.setAttribute('title', connected ? 'Connected' : 'Connection lost');
    };

    /**
     * Small transient message, used for all-out alerts and rejected actions.
     */
    LiveSession.prototype.toast = function (message, tone) {
        if (!message) {
            return;
        }

        let stack = document.querySelector('.toast-stack');

        if (!stack) {
            stack = document.createElement('div');
            stack.className = 'toast-stack';
            document.body.appendChild(stack);
        }

        const alert = document.createElement('div');
        alert.className = 'alert alert-' + (tone || 'info') + ' shadow alert-dismissible fade show';
        alert.setAttribute('role', 'alert');
        alert.textContent = message;

        const close = document.createElement('button');
        close.type = 'button';
        close.className = 'btn-close';
        close.setAttribute('aria-label', 'Close');
        close.addEventListener('click', () => alert.remove());
        alert.appendChild(close);

        stack.appendChild(alert);

        window.setTimeout(function () {
            alert.classList.remove('show');
            window.setTimeout(() => alert.remove(), 300);
        }, 5000);
    };

    /**
     * Write text into every element matching a selector.
     */
    function setText(selector, value, root) {
        (root || document).querySelectorAll(selector).forEach((node) => {
            const next = String(value);

            if (node.textContent !== next) {
                node.textContent = next;
            }
        });
    }

    window.KtmsLive = {
        formatClock: formatClock,
        setText: setText,
        session: function (options) {
            return new LiveSession(options);
        },
    };
})(window, document);
