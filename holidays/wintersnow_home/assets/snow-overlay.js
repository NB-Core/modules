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

    function Snowflake(width, height, options) {
        this.reset(width, height, options);
    }

    Snowflake.prototype.reset = function (width, height, options) {
        this.x = Math.random() * width;
        this.y = Math.random() * height;
        this.radius = (options.minRadius || 1) + Math.random() * ((options.maxRadius || 3) - (options.minRadius || 1));
        this.speedY = (options.minSpeedY || 0.5) + Math.random() * ((options.maxSpeedY || 2) - (options.minSpeedY || 0.5));
        this.speedX = (options.minSpeedX || -0.5) + Math.random() * ((options.maxSpeedX || 0.5) - (options.minSpeedX || -0.5));
        this.drift = Math.random() * Math.PI * 2;
    };

    Snowflake.prototype.update = function (width, height) {
        this.y += this.speedY;
        this.x += Math.sin(this.drift) + this.speedX;
        this.drift += 0.01;

        if (this.y > height + this.radius) {
            this.y = -this.radius;
            this.x = Math.random() * width;
        }

        if (this.x > width + this.radius) {
            this.x = -this.radius;
        } else if (this.x < -this.radius) {
            this.x = width + this.radius;
        }
    };

    function SnowOverlay(configuration) {
        this.configuration = configuration || {};
        this.snowflakeCount = Math.max(0, Number(this.configuration.snowflakeCount) || 100);
        this.color = this.configuration.snowflakeColor || "#ffffff";
        this.zIndex = typeof this.configuration.zIndex === "number" ? this.configuration.zIndex : 9999;
        this.canvas = null;
        this.context = null;
        this.snowflakes = [];
        this.running = false;
        this.frameId = null;
        this.boundLoop = this.loop.bind(this);
        this.handleResize = this.onResize.bind(this);
    }

    SnowOverlay.prototype.start = function () {
        if (this.running) {
            return;
        }

        if (!document.body) {
            return;
        }

        this.canvas = createCanvas(this.zIndex);
        this.context = resizeCanvas(this.canvas);
        this.createSnowflakes();
        window.addEventListener("resize", this.handleResize);
        this.running = true;
        this.frameId = window.requestAnimationFrame(this.boundLoop);
    };

    SnowOverlay.prototype.stop = function () {
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
        this.snowflakes = [];
    };

    SnowOverlay.prototype.createSnowflakes = function () {
        var width = this.canvas.width;
        var height = this.canvas.height;
        var options = {
            minRadius: this.configuration.minRadius,
            maxRadius: this.configuration.maxRadius,
            minSpeedY: this.configuration.minSpeedY,
            maxSpeedY: this.configuration.maxSpeedY,
            minSpeedX: this.configuration.minSpeedX,
            maxSpeedX: this.configuration.maxSpeedX
        };

        this.snowflakes = [];
        for (var i = 0; i < this.snowflakeCount; i += 1) {
            this.snowflakes.push(new Snowflake(width, height, options));
        }
    };

    SnowOverlay.prototype.onResize = function () {
        if (!this.canvas) {
            return;
        }
        this.context = resizeCanvas(this.canvas);
        this.createSnowflakes();
    };

    SnowOverlay.prototype.loop = function () {
        if (!this.running || !this.context) {
            return;
        }

        var canvas = this.canvas;
        var context = this.context;
        var width = canvas.width;
        var height = canvas.height;

        context.clearRect(0, 0, width, height);
        context.fillStyle = this.color;

        for (var i = 0; i < this.snowflakes.length; i += 1) {
            var flake = this.snowflakes[i];
            flake.update(width, height);
            context.beginPath();
            context.arc(flake.x, flake.y, flake.radius, 0, Math.PI * 2, false);
            context.fill();
        }

        this.frameId = window.requestAnimationFrame(this.boundLoop);
    };

    window.SnowOverlay = SnowOverlay;
}());
