(function () {
    "use strict";

    function createCanvas(zIndex) {
        var canvas = document.createElement("canvas");
        canvas.setAttribute("aria-hidden", "true");
        canvas.style.position = "fixed";
        canvas.style.pointerEvents = "none";
        canvas.style.top = "0";
        canvas.style.left = "0";
        canvas.style.width = "100%";
        canvas.style.height = "100%";
        canvas.style.zIndex = String(typeof zIndex === "number" ? zIndex : 9999);
        document.body.appendChild(canvas);
        return canvas;
    }

    function resizeCanvas(canvas) {
        var ratio = window.devicePixelRatio || 1;
        canvas.width = Math.floor(window.innerWidth * ratio);
        canvas.height = Math.floor(window.innerHeight * ratio);
        var context = canvas.getContext("2d");
        context.setTransform(ratio, 0, 0, ratio, 0, 0);
        return context;
    }

    function createParticle(x, y, color, speed, decayRange) {
        var angle = Math.random() * Math.PI * 2;
        var finalSpeed = typeof speed === "number" ? speed : Math.random() * 4 + 1;
        var minDecay = decayRange && typeof decayRange.min === "number" ? decayRange.min : 0.015;
        var maxDecay = decayRange && typeof decayRange.max === "number" ? decayRange.max : 0.035;
        var sizeBias = Math.pow(Math.random(), 1.4);
        var size = 1 + sizeBias * 2.2;
        var baseAlpha = 0.5 + Math.random() * 0.5;
        if (size < 1.6) {
            baseAlpha = 0.8 + Math.random() * 0.2;
        }
        return {
            x: x,
            y: y,
            vx: Math.cos(angle) * finalSpeed,
            vy: Math.sin(angle) * finalSpeed,
            alpha: 1,
            baseAlpha: baseAlpha,
            decay: Math.random() * (maxDecay - minDecay) + minDecay,
            color: color,
            size: size
        };
    }

    function FireworksOverlay(configuration) {
        this.configuration = configuration || {};
        this.colors = Array.isArray(this.configuration.colors) && this.configuration.colors.length > 0 ? this.configuration.colors : ["#ff2d00", "#ffee00", "#00c7ff"];
        this.particleCount = Math.max(1, Number(this.configuration.particleCount) || 120);
        // Expect data-intervals (kebab-case) to supply a comma-separated list of millisecond values.
        this.intervals = Array.isArray(this.configuration.intervals) ? this.configuration.intervals.filter(function (interval) {
            return typeof interval === "number" && !isNaN(interval);
        }) : [];
        this.speedIntervals = Array.isArray(this.configuration.speedIntervals) ? this.configuration.speedIntervals.filter(function (interval) {
            return typeof interval === "number" && !isNaN(interval);
        }) : [];
        var configuredSpeedRange = Array.isArray(this.configuration.speedRange) ? this.configuration.speedRange.filter(function (speed) {
            return typeof speed === "number" && !isNaN(speed);
        }) : [];
        var configuredDecayRange = Array.isArray(this.configuration.decayRange) ? this.configuration.decayRange.filter(function (decay) {
            return typeof decay === "number" && !isNaN(decay);
        }) : [];
        var fallbackSpeedRange = [1, 5];
        var fallbackDecayRange = [0.015, 0.035];
        if (configuredSpeedRange.length >= 2) {
            fallbackSpeedRange = configuredSpeedRange.slice(0, 2);
        }
        if (configuredDecayRange.length >= 2) {
            fallbackDecayRange = configuredDecayRange.slice(0, 2);
        }
        this.speedRange = {
            min: Math.min(fallbackSpeedRange[0], fallbackSpeedRange[1]),
            max: Math.max(fallbackSpeedRange[0], fallbackSpeedRange[1])
        };
        this.decayRange = {
            min: Math.min(fallbackDecayRange[0], fallbackDecayRange[1]),
            max: Math.max(fallbackDecayRange[0], fallbackDecayRange[1])
        };
        this.defaultInterval = Math.max(200, this.intervals.length > 0 ? 600 : Number(this.configuration.interval) || 600);
        this.launchInterval = this.defaultInterval;
        this.gravity = typeof this.configuration.gravity === "number" ? this.configuration.gravity : 0.05;
        this.zIndex = typeof this.configuration.zIndex === "number" ? this.configuration.zIndex : 9999;
        this.canvas = null;
        this.context = null;
        this.particles = [];
        this.lastLaunch = 0;
        this.running = false;
        this.frameId = null;
        this.boundLoop = this.loop.bind(this);
        this.handleResize = this.onResize.bind(this);
    }

    FireworksOverlay.prototype.getLaunchInterval = function () {
        if (!this.intervals.length) {
            return this.defaultInterval;
        }
        var candidate = this.intervals[Math.floor(Math.random() * this.intervals.length)];
        return Math.max(200, candidate || this.defaultInterval);
    };

    FireworksOverlay.prototype.getExplosionSpeed = function () {
        if (this.speedIntervals.length) {
            var intervalSpeed = this.speedIntervals[Math.floor(Math.random() * this.speedIntervals.length)];
            var intervalVariance = 0.6 + Math.random() * 0.8;
            return Math.max(0.1, intervalSpeed * intervalVariance);
        }
        var range = this.speedRange;
        var biased = Math.pow(Math.random(), 1.7);
        return biased * (range.max - range.min) + range.min;
    };

    FireworksOverlay.prototype.start = function () {
        if (this.running) {
            return;
        }

        if (!document.body) {
            return;
        }

        this.canvas = createCanvas(this.zIndex);
        this.context = resizeCanvas(this.canvas);
        window.addEventListener("resize", this.handleResize);
        this.running = true;
        this.lastLaunch = window.performance.now();
        this.launchInterval = this.getLaunchInterval();
        this.frameId = window.requestAnimationFrame(this.boundLoop);
    };

    FireworksOverlay.prototype.stop = function () {
        this.running = false;
        if (this.frameId) {
            window.cancelAnimationFrame(this.frameId);
            this.frameId = null;
        }
        window.removeEventListener("resize", this.handleResize);
        if (this.canvas && this.canvas.parentNode) {
            this.canvas.parentNode.removeChild(this.canvas);
        }
        this.canvas = null;
        this.context = null;
        this.particles = [];
    };

    FireworksOverlay.prototype.emitParticles = function (x, y, color, count) {
        for (var i = 0; i < count; i += 1) {
            this.particles.push(createParticle(x, y, color, this.getExplosionSpeed(), this.decayRange));
        }
    };

    FireworksOverlay.prototype.launchFirework = function () {
        if (!this.canvas) {
            return;
        }

        var x = Math.random() * this.canvas.width;
        var y = Math.random() * (this.canvas.height * 0.5);
        var color = this.colors[Math.floor(Math.random() * this.colors.length)];
        var bursts = 2 + Math.floor(Math.random() * 2);
        var remaining = this.particleCount;
        var self = this;
        for (var i = 0; i < bursts; i += 1) {
            var slice = Math.max(1, Math.round(remaining / (bursts - i)));
            remaining -= slice;
            var delay = i === 0 ? 0 : 60 + Math.random() * 80;
            window.setTimeout(function (count) {
                if (!self.running || !self.canvas) {
                    return;
                }
                self.emitParticles(x, y, color, count);
            }.bind(null, slice), delay);
        }
    };

    FireworksOverlay.prototype.onResize = function () {
        if (!this.canvas) {
            return;
        }
        this.context = resizeCanvas(this.canvas);
    };

    FireworksOverlay.prototype.updateParticles = function () {
        var gravity = this.gravity;
        var i = 0;
        while (i < this.particles.length) {
            var particle = this.particles[i];
            particle.x += particle.vx;
            particle.y += particle.vy;
            particle.vy += gravity;
            particle.alpha -= particle.decay;

            if (particle.alpha <= 0 || particle.y > this.canvas.height) {
                this.particles.splice(i, 1);
            } else {
                i += 1;
            }
        }
    };

    FireworksOverlay.prototype.renderParticles = function () {
        var context = this.context;
        context.save();
        context.globalCompositeOperation = "lighter";
        for (var i = 0; i < this.particles.length; i += 1) {
            var particle = this.particles[i];
            context.globalAlpha = Math.max(particle.alpha * particle.baseAlpha, 0);
            context.fillStyle = particle.color;
            context.beginPath();
            context.arc(particle.x, particle.y, particle.size, 0, Math.PI * 2, false);
            context.fill();
        }
        context.restore();
    };

    FireworksOverlay.prototype.loop = function (timestamp) {
        if (!this.running || !this.context) {
            return;
        }

        var context = this.context;
        var canvas = this.canvas;
        context.clearRect(0, 0, canvas.width, canvas.height);

        if (timestamp - this.lastLaunch >= this.launchInterval) {
            this.launchFirework();
            this.lastLaunch = timestamp;
            this.launchInterval = this.getLaunchInterval();
        }

        this.updateParticles();
        this.renderParticles();

        this.frameId = window.requestAnimationFrame(this.boundLoop);
    };

    window.FireworksOverlay = FireworksOverlay;
}());
