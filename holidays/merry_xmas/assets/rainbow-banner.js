(() => {
    const selectors = ['[data-rainbow-banner]', '#merry-xmas-banner', '#mysterygems-closure'];
    const elements = [];

    selectors.forEach((selector) => {
        document.querySelectorAll(selector).forEach((element) => {
            if (element) {
                elements.push(element);
            }
        });
    });

    const uniqueElements = Array.from(new Set(elements));

    if (!uniqueElements.length) {
        return;
    }

    const styleId = 'rainbow-banner-style';

    if (!document.getElementById(styleId)) {
        const style = document.createElement('style');

        style.id = styleId;
        style.textContent = `
[data-rainbow-banner] {
    --rainbow-duration: 8s;
    --rainbow-fallback-color: #ff3b30;
    display: inline-block;
    background: linear-gradient(90deg, #ff3b30, #ff9500, #ffcc00, #34c759, #5ac8fa, #007aff, #af52de, #ff3b30);
    background-size: 400% 100%;
    color: var(--rainbow-fallback-color);
    animation: rainbow-banner-shift var(--rainbow-duration) linear infinite;
}

@supports ((background-clip: text) or (-webkit-background-clip: text)) {
    [data-rainbow-banner] {
        color: transparent;
        background-clip: text;
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
    }
}

@keyframes rainbow-banner-shift {
    0% {
        background-position: 0% 50%;
    }

    100% {
        background-position: 100% 50%;
    }
}
`;

        document.head.appendChild(style);
    }

    uniqueElements.forEach((element) => {
        if (!element.hasAttribute('data-rainbow-banner')) {
            element.setAttribute('data-rainbow-banner', '');
        }

        if (!element.dataset.rainbowBannerInitialized) {
            element.dataset.rainbowBannerInitialized = 'true';
        }
    });
})();
