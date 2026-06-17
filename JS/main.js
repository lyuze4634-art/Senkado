(function () {
    const confirmForms = document.querySelectorAll('form[data-confirm]');

    confirmForms.forEach((form) => {
        form.addEventListener('submit', (event) => {
            const message = form.getAttribute('data-confirm') || '実行しますか。';
            if (!window.confirm(message)) {
                event.preventDefault();
            }
        });
    });

    const mainImage = document.querySelector('[data-gallery-main]');
    const previewButton = document.querySelector('[data-lightbox-trigger]');
    const thumbnails = document.querySelectorAll('[data-gallery-thumb]');
    const lightbox = document.querySelector('[data-lightbox]');
    const lightboxImage = document.querySelector('[data-lightbox-image]');
    const lightboxClose = document.querySelector('[data-lightbox-close]');
    const minLightboxScale = 0.35;
    const maxLightboxScale = 6;
    let lightboxScale = 1;
    let lightboxOffsetX = 0;
    let lightboxOffsetY = 0;
    let isDraggingLightbox = false;
    let dragStartX = 0;
    let dragStartY = 0;
    let dragStartOffsetX = 0;
    let dragStartOffsetY = 0;
    let didDragLightbox = false;
    let ignoreNextLightboxClick = false;

    function setActiveThumbnail(selected) {
        thumbnails.forEach((thumb) => thumb.classList.toggle('is-active', thumb === selected));
    }

    function setPreview(src, alt) {
        if (mainImage) {
            mainImage.src = src;
            mainImage.alt = alt;
        }

        if (previewButton) {
            previewButton.setAttribute('data-image-src', src);
            previewButton.setAttribute('data-image-alt', alt);
        }
    }

    thumbnails.forEach((thumb, index) => {
        if (index === 0) {
            thumb.classList.add('is-active');
        }

        thumb.addEventListener('click', () => {
            const src = thumb.getAttribute('data-image-src');
            const alt = thumb.getAttribute('data-image-alt') || '';

            if (src) {
                setPreview(src, alt);
                setActiveThumbnail(thumb);
            }
        });
    });

    function clamp(value, min, max) {
        return Math.min(Math.max(value, min), max);
    }

    function applyLightboxTransform() {
        if (!lightboxImage) {
            return;
        }

        lightboxImage.style.transform = `translate3d(${lightboxOffsetX}px, ${lightboxOffsetY}px, 0) scale(${lightboxScale})`;
    }

    function resetLightboxView() {
        lightboxScale = 1;
        lightboxOffsetX = 0;
        lightboxOffsetY = 0;
        isDraggingLightbox = false;

        if (lightboxImage) {
            lightboxImage.classList.remove('is-dragging');
        }

        applyLightboxTransform();
    }

    function openLightbox(src, alt) {
        if (!lightbox || !lightboxImage || !src) {
            return;
        }

        lightboxImage.src = src;
        lightboxImage.alt = alt || '';
        resetLightboxView();
        lightbox.hidden = false;
        document.body.style.overflow = 'hidden';
    }

    function closeLightbox() {
        if (!lightbox || !lightboxImage) {
            return;
        }

        lightbox.hidden = true;
        lightboxImage.src = '';
        document.body.style.overflow = '';
        resetLightboxView();
    }

    if (previewButton) {
        previewButton.addEventListener('click', () => {
            openLightbox(previewButton.getAttribute('data-image-src'), previewButton.getAttribute('data-image-alt'));
        });
    }

    if (lightboxClose) {
        lightboxClose.addEventListener('click', closeLightbox);
    }

    if (lightbox) {
        lightbox.addEventListener('click', (event) => {
            if (ignoreNextLightboxClick) {
                ignoreNextLightboxClick = false;
                return;
            }

            if (event.target === lightbox) {
                closeLightbox();
            }
        });

        lightbox.addEventListener('wheel', (event) => {
            if (lightbox.hidden) {
                return;
            }

            event.preventDefault();
            const zoomFactor = event.deltaY < 0 ? 1.12 : 0.88;
            lightboxScale = clamp(lightboxScale * zoomFactor, minLightboxScale, maxLightboxScale);
            applyLightboxTransform();
        }, { passive: false });
    }

    if (lightboxImage) {
        lightboxImage.addEventListener('dragstart', (event) => {
            event.preventDefault();
        });

        lightboxImage.addEventListener('pointerdown', (event) => {
            if (event.button !== 0) {
                return;
            }

            event.preventDefault();
            isDraggingLightbox = true;
            dragStartX = event.clientX;
            dragStartY = event.clientY;
            dragStartOffsetX = lightboxOffsetX;
            dragStartOffsetY = lightboxOffsetY;
            lightboxImage.classList.add('is-dragging');
            lightboxImage.setPointerCapture(event.pointerId);
        });

        lightboxImage.addEventListener('pointermove', (event) => {
            if (!isDraggingLightbox) {
                return;
            }

            lightboxOffsetX = dragStartOffsetX + event.clientX - dragStartX;
            lightboxOffsetY = dragStartOffsetY + event.clientY - dragStartY;
            didDragLightbox = didDragLightbox || Math.abs(event.clientX - dragStartX) > 3 || Math.abs(event.clientY - dragStartY) > 3;
            applyLightboxTransform();
        });

        lightboxImage.addEventListener('pointerup', (event) => {
            isDraggingLightbox = false;
            lightboxImage.classList.remove('is-dragging');
            ignoreNextLightboxClick = didDragLightbox;
            didDragLightbox = false;

            if (lightboxImage.hasPointerCapture(event.pointerId)) {
                lightboxImage.releasePointerCapture(event.pointerId);
            }
        });

        lightboxImage.addEventListener('pointercancel', () => {
            isDraggingLightbox = false;
            lightboxImage.classList.remove('is-dragging');
        });
    }

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') {
            closeLightbox();
        }
    });
})();
