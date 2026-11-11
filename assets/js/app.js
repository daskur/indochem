document.addEventListener('DOMContentLoaded', () => {
    const toggle = document.querySelector('.nav-toggle');
    const menu = document.querySelector('.nav-menu');

    if (toggle && menu) {
        toggle.addEventListener('click', () => {
            const isOpen = menu.classList.toggle('is-open');
            toggle.setAttribute('aria-expanded', String(isOpen));
        });

        menu.addEventListener('click', (event) => {
            if (event.target.closest('a')) {
                menu.classList.remove('is-open');
                toggle.setAttribute('aria-expanded', 'false');
            }
        });

        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape') {
                menu.classList.remove('is-open');
                toggle.setAttribute('aria-expanded', 'false');
                toggle.focus();
            }
        });

        window.addEventListener('resize', () => {
            if (window.innerWidth > 900) {
                menu.classList.remove('is-open');
                toggle.setAttribute('aria-expanded', 'false');
            }
        });
    }

    const moleculeLauncher = document.querySelector('#molecule-open');
    const moleculeViewer = document.querySelector('#molecule-viewer');
    const moleculeClose = moleculeViewer?.querySelector('.molecule-close');
    const moleculeCanvas = moleculeViewer?.querySelector('#molecule-canvas');

    if (!moleculeLauncher || !moleculeViewer || !moleculeClose || !moleculeCanvas) {
        return;
    }

    const context = moleculeCanvas.getContext('2d');
    if (!context) {
        moleculeLauncher.disabled = true;
        moleculeLauncher.setAttribute('aria-label', '3D molecule viewer is unavailable in this browser');
        return;
    }

    const atoms = [
        { element: 'C', x: 0, y: 0, z: 0 },
        { element: 'H', x: 1, y: 1, z: 1 },
        { element: 'H', x: 1, y: -1, z: -1 },
        { element: 'H', x: -1, y: 1, z: -1 },
        { element: 'H', x: -1, y: -1, z: 1 }
    ];
    const bonds = [[0, 1], [0, 2], [0, 3], [0, 4]];
    const initialRotation = { x: -0.3, y: 0.55 };
    let rotation = { ...initialRotation };
    let activePointer = null;
    let previousPointer = null;
    let animationFrame = null;
    const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    function project(atom) {
        const cosX = Math.cos(rotation.x);
        const sinX = Math.sin(rotation.x);
        const cosY = Math.cos(rotation.y);
        const sinY = Math.sin(rotation.y);
        const rotatedY = atom.y * cosX - atom.z * sinX;
        const rotatedZ = atom.y * sinX + atom.z * cosX;
        const rotatedX = atom.x * cosY + rotatedZ * sinY;
        const depth = -atom.x * sinY + rotatedZ * cosY;
        const scale = 270 / (4.8 - depth);
        const bounds = moleculeCanvas.getBoundingClientRect();

        return {
            x: bounds.width / 2 + rotatedX * scale,
            y: bounds.height / 2 + rotatedY * scale,
            z: depth,
            scale
        };
    }

    function drawAtom(atom, point) {
        const radius = (atom.element === 'C' ? 18 : 13) * (point.scale / 30);
        const gradient = context.createRadialGradient(
            point.x - radius * 0.35,
            point.y - radius * 0.4,
            radius * 0.1,
            point.x,
            point.y,
            radius
        );
        if (atom.element === 'C') {
            gradient.addColorStop(0, '#67c9bd');
            gradient.addColorStop(1, '#087f7a');
        } else {
            gradient.addColorStop(0, '#ffffff');
            gradient.addColorStop(1, '#c6d5de');
        }

        context.beginPath();
        context.arc(point.x, point.y, radius, 0, Math.PI * 2);
        context.fillStyle = gradient;
        context.fill();
        context.lineWidth = 1.5;
        context.strokeStyle = atom.element === 'C' ? '#075d59' : '#718596';
        context.stroke();
        context.fillStyle = atom.element === 'C' ? '#ffffff' : '#17324d';
        context.font = `700 ${Math.max(10, radius * 0.85)}px Arial, sans-serif`;
        context.textAlign = 'center';
        context.textBaseline = 'middle';
        context.fillText(atom.element, point.x, point.y + 0.5);
    }

    function drawMolecule() {
        const bounds = moleculeCanvas.getBoundingClientRect();
        if (!bounds.width || !bounds.height) {
            return;
        }

        const pixelRatio = window.devicePixelRatio || 1;
        const canvasWidth = Math.round(bounds.width * pixelRatio);
        const canvasHeight = Math.round(bounds.height * pixelRatio);
        if (moleculeCanvas.width !== canvasWidth || moleculeCanvas.height !== canvasHeight) {
            moleculeCanvas.width = canvasWidth;
            moleculeCanvas.height = canvasHeight;
        }
        context.setTransform(pixelRatio, 0, 0, pixelRatio, 0, 0);
        context.clearRect(0, 0, bounds.width, bounds.height);

        const points = atoms.map(project);
        bonds.forEach(([start, end]) => {
            const first = points[start];
            const second = points[end];
            const depth = (first.z + second.z) / 2;
            context.beginPath();
            context.moveTo(first.x, first.y);
            context.lineTo(second.x, second.y);
            context.lineWidth = 5 + (depth + 1) * 1.2;
            context.lineCap = 'round';
            context.strokeStyle = '#8799a6';
            context.stroke();
        });

        atoms
            .map((atom, index) => ({ atom, point: points[index] }))
            .sort((first, second) => first.point.z - second.point.z)
            .forEach(({ atom, point }) => drawAtom(atom, point));
    }

    function animateMolecule() {
        if (moleculeViewer.hidden || reducedMotion) {
            animationFrame = null;
            return;
        }

        if (activePointer === null) {
            rotation.y += 0.004;
            drawMolecule();
        }
        animationFrame = window.requestAnimationFrame(animateMolecule);
    }

    function startMoleculeAnimation() {
        if (!reducedMotion && animationFrame === null) {
            animationFrame = window.requestAnimationFrame(animateMolecule);
        }
    }

    function closeMoleculeViewer() {
        moleculeViewer.hidden = true;
        moleculeLauncher.setAttribute('aria-expanded', 'false');
        document.body.classList.remove('molecule-open');
        if (animationFrame !== null) {
            window.cancelAnimationFrame(animationFrame);
            animationFrame = null;
        }
        moleculeLauncher.focus();
    }

    moleculeLauncher.addEventListener('click', () => {
        moleculeViewer.hidden = false;
        moleculeLauncher.setAttribute('aria-expanded', 'true');
        document.body.classList.add('molecule-open');
        window.requestAnimationFrame(() => {
            drawMolecule();
            startMoleculeAnimation();
            moleculeCanvas.focus();
        });
    });

    moleculeClose.addEventListener('click', closeMoleculeViewer);
    moleculeViewer.addEventListener('click', (event) => {
        if (event.target === moleculeViewer) {
            closeMoleculeViewer();
        }
    });

    document.addEventListener('keydown', (event) => {
        if (moleculeViewer.hidden) {
            return;
        }

        if (event.key === 'Escape') {
            event.preventDefault();
            closeMoleculeViewer();
            return;
        }

        if (event.target === moleculeCanvas) {
            const movement = 0.12;
            if (event.key === 'ArrowLeft') rotation.y -= movement;
            else if (event.key === 'ArrowRight') rotation.y += movement;
            else if (event.key === 'ArrowUp') rotation.x -= movement;
            else if (event.key === 'ArrowDown') rotation.x += movement;
            else if (event.key === 'Home') rotation = { ...initialRotation };
            if (['ArrowLeft', 'ArrowRight', 'ArrowUp', 'ArrowDown', 'Home'].includes(event.key)) {
                event.preventDefault();
                drawMolecule();
                return;
            }
        }

        if (event.key === 'Tab') {
            const focusable = [moleculeClose, moleculeCanvas];
            const first = focusable[0];
            const last = focusable[focusable.length - 1];
            if (event.shiftKey && (document.activeElement === first || !moleculeViewer.contains(document.activeElement))) {
                event.preventDefault();
                last.focus();
            } else if (!event.shiftKey && (document.activeElement === last || !moleculeViewer.contains(document.activeElement))) {
                event.preventDefault();
                first.focus();
            }
        }
    });

    moleculeCanvas.addEventListener('pointerdown', (event) => {
        if (event.button !== 0) {
            return;
        }
        activePointer = event.pointerId;
        previousPointer = { x: event.clientX, y: event.clientY };
        moleculeCanvas.setPointerCapture(event.pointerId);
        moleculeCanvas.focus({ preventScroll: true });
    });

    moleculeCanvas.addEventListener('pointermove', (event) => {
        if (event.pointerId !== activePointer || !previousPointer) {
            return;
        }
        rotation.y += (event.clientX - previousPointer.x) * 0.01;
        rotation.x += (event.clientY - previousPointer.y) * 0.01;
        previousPointer = { x: event.clientX, y: event.clientY };
        drawMolecule();
    });

    moleculeCanvas.addEventListener('pointerup', () => {
        activePointer = null;
        previousPointer = null;
    });
    moleculeCanvas.addEventListener('pointercancel', () => {
        activePointer = null;
        previousPointer = null;
    });
    window.addEventListener('resize', () => {
        if (!moleculeViewer.hidden) {
            drawMolecule();
        }
    });
});
