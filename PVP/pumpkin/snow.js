(() => {
    const PUMPKIN_IMAGES = [
        "modules/pumpkin/h1.gif",
        "modules/pumpkin/h2.gif",
        "modules/pumpkin/h3.gif"
    ];

    const PUMPKIN_COUNT = 15;
    const FALL_RESET_OFFSET = 60;

    const pumpkins = [];

    const createPumpkin = (index) => {
        const image = document.createElement("img");
        image.src = PUMPKIN_IMAGES[index % PUMPKIN_IMAGES.length];
        image.alt = "";
        image.style.position = "fixed";
        image.style.top = "0";
        image.style.left = "0";
        image.style.pointerEvents = "none";
        image.style.willChange = "transform";
        image.style.zIndex = "9999";

        document.body.appendChild(image);

        return {
            element: image,
            x: Math.random() * window.innerWidth,
            y: Math.random() * window.innerHeight,
            amplitude: 10 + Math.random() * 20,
            horizontalPhase: Math.random() * Math.PI * 2,
            horizontalSpeed: 0.01 + Math.random() / 12,
            verticalSpeed: 0.4 + Math.random() * 0.8
        };
    };

    const updatePumpkin = (pumpkin) => {
        pumpkin.horizontalPhase += pumpkin.horizontalSpeed;
        pumpkin.y -= pumpkin.verticalSpeed;

        if (pumpkin.y < -FALL_RESET_OFFSET) {
            pumpkin.x = Math.random() * window.innerWidth;
            pumpkin.y = window.innerHeight + FALL_RESET_OFFSET;
            pumpkin.amplitude = 10 + Math.random() * 20;
            pumpkin.horizontalSpeed = 0.01 + Math.random() / 12;
            pumpkin.verticalSpeed = 0.4 + Math.random() * 0.8;
        }

        const offsetX = pumpkin.x + Math.sin(pumpkin.horizontalPhase) * pumpkin.amplitude;
        pumpkin.element.style.transform = `translate3d(${offsetX.toFixed(2)}px, ${pumpkin.y.toFixed(2)}px, 0)`;
    };

    const animate = () => {
        pumpkins.forEach(updatePumpkin);
        requestAnimationFrame(animate);
    };

    const onResize = () => {
        pumpkins.forEach((pumpkin) => {
            pumpkin.x = Math.max(0, Math.min(pumpkin.x, window.innerWidth));
            pumpkin.y = Math.min(pumpkin.y, window.innerHeight + FALL_RESET_OFFSET);
        });
    };

    const init = () => {
        if (!document.body) {
            return;
        }

        for (let index = 0; index < PUMPKIN_COUNT; index += 1) {
            pumpkins.push(createPumpkin(index));
        }

        window.addEventListener("resize", onResize);
        animate();
    };

    if (document.readyState === "loading") {
        document.addEventListener("DOMContentLoaded", init, { once: true });
    } else {
        init();
    }
})();
