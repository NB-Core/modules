(function () {
    "use strict";

    function parseNumber(value) {
        if (value === undefined) {
            return undefined;
        }
        var number = Number(value);
        return isNaN(number) ? undefined : number;
    }

    function parseArray(value) {
        if (!value) {
            return undefined;
        }
        var delimiter = ",";
        if (value.indexOf("|") !== -1) {
            delimiter = "|";
        } else if (value.indexOf(";") !== -1) {
            delimiter = ";";
        }
        var items = value.split(delimiter).map(function (item) {
            return item.trim();
        }).filter(function (item) {
            return item.length > 0;
        });
        return items.length > 0 ? items : undefined;
    }

    function parseNumberArray(value) {
        var items = parseArray(value);
        if (!items) {
            return undefined;
        }
        var numbers = items.map(function (item) {
            return parseNumber(item);
        }).filter(function (item) {
            return typeof item === "number";
        });
        return numbers.length > 0 ? numbers : undefined;
    }

    function buildConfiguration(placeholder) {
        var dataset = placeholder.dataset;
        var configuration = {};

        if (dataset.snowflakeCount) {
            configuration.snowflakeCount = parseNumber(dataset.snowflakeCount);
        }
        if (dataset.snowflakeColor) {
            configuration.snowflakeColor = dataset.snowflakeColor;
        }
        if (dataset.zIndex) {
            configuration.zIndex = parseNumber(dataset.zIndex);
        }
        if (dataset.particleCount) {
            configuration.particleCount = parseNumber(dataset.particleCount);
        }
        if (dataset.interval) {
            configuration.interval = parseNumber(dataset.interval);
        }
        if (dataset.intervals) {
            configuration.intervals = parseNumberArray(dataset.intervals);
        }
        if (dataset.speedRange) {
            configuration.speedRange = parseNumberArray(dataset.speedRange);
        }
        if (dataset.decayRange) {
            configuration.decayRange = parseNumberArray(dataset.decayRange);
        }
        if (dataset.speedIntervals) {
            configuration.speedIntervals = parseNumberArray(dataset.speedIntervals);
        }
        if (dataset.colors) {
            configuration.colors = parseArray(dataset.colors);
        }
        if (dataset.gravity) {
            configuration.gravity = parseNumber(dataset.gravity);
        }

        return configuration;
    }

    function startOverlay(placeholder) {
        var mode = placeholder.dataset.overlayMode;
        var configuration = buildConfiguration(placeholder);

        if (mode === "snow" && window.SnowOverlay) {
            var snow = new window.SnowOverlay(configuration);
            snow.start();
            placeholder.__wintersnow_instance__ = snow;
        } else if (mode === "fireworks" && window.FireworksOverlay) {
            var fireworks = new window.FireworksOverlay(configuration);
            fireworks.start();
            placeholder.__wintersnow_instance__ = fireworks;
        }
    }

    function ready(handler) {
        if (document.readyState === "loading") {
            document.addEventListener("DOMContentLoaded", handler);
        } else {
            handler();
        }
    }

    ready(function () {
        var placeholder = document.querySelector('[data-overlay-module="wintersnow_home"]');
        if (!placeholder) {
            return;
        }
        startOverlay(placeholder);
    });
}());
