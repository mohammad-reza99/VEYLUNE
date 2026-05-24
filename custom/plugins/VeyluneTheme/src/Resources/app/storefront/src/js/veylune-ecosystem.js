const initVeyluneEcosystem = () => {
    const ecosystem = document.querySelector('[data-veylune-ecosystem]');
    const body = document.body;
    const reducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    if (body.dataset.veyluneEcosystemInitialized === 'true') {
        return;
    }

    body.dataset.veyluneEcosystemInitialized = 'true';

    const storageKey = 'veylune.world.memory';
    const moodLabels = {
        limestone: 'Limestone Morning',
        bronze: 'Bronze Dusk',
        charcoal: 'Charcoal Evening'
    };

    let memory = {
        mood: 'limestone',
        interest: ''
    };

    try {
        memory = { ...memory, ...JSON.parse(window.localStorage.getItem(storageKey) || '{}') };
    } catch (error) {
        memory = { mood: 'limestone', interest: '' };
    }

    const saveMemory = () => {
        try {
            window.localStorage.setItem(storageKey, JSON.stringify(memory));
        } catch (error) {
            // Local storage can be unavailable in private contexts; the experience still works without it.
        }
    };

    const setMood = (mood) => {
        memory.mood = moodLabels[mood] ? mood : 'limestone';
        body.classList.remove('veylune-mood-limestone', 'veylune-mood-bronze', 'veylune-mood-charcoal');
        body.classList.add(`veylune-mood-${memory.mood}`);

        if (ecosystem) {
            ecosystem.setAttribute('data-veylune-mood', memory.mood);
        }

        document.querySelectorAll('[data-veylune-mood]').forEach((button) => {
            button.classList.toggle('is-active', button.dataset.veyluneMood === memory.mood);
        });

        const moodOutput = document.querySelector('[data-veylune-memory-mood]');
        if (moodOutput) {
            moodOutput.textContent = moodLabels[memory.mood];
        }

        saveMemory();
    };

    const setInterest = (interest) => {
        if (!interest) {
            return;
        }

        memory.interest = interest;
        document.querySelectorAll('[data-veylune-interest]').forEach((item) => {
            item.classList.toggle('is-selected', item.dataset.veyluneInterest === interest);
        });

        const interestOutput = document.querySelector('[data-veylune-memory-interest]');
        if (interestOutput) {
            const label = interest.replace(/-/g, ' ');
            interestOutput.textContent = `Your archive now leans toward ${label}. VEYLUNE will keep the tone quiet, curated, and spatial.`;
        }

        saveMemory();
    };

    setMood(memory.mood);
    setInterest(memory.interest);

    document.querySelectorAll('[data-veylune-mood]').forEach((button) => {
        button.addEventListener('click', () => setMood(button.dataset.veyluneMood));
    });

    document.querySelectorAll('[data-veylune-interest]').forEach((item) => {
        item.addEventListener('click', () => setInterest(item.dataset.veyluneInterest));
    });

    const zones = [...document.querySelectorAll('[data-veylune-atmosphere-zone]')];

    if (!reducedMotion && zones.length && 'IntersectionObserver' in window) {
        const observer = new IntersectionObserver((entries) => {
            const active = entries
                .filter((entry) => entry.isIntersecting)
                .sort((a, b) => b.intersectionRatio - a.intersectionRatio)[0];

            if (active?.target?.dataset.veyluneAtmosphereZone) {
                body.setAttribute('data-veylune-zone', active.target.dataset.veyluneAtmosphereZone);
            }
        }, {
            threshold: [0.25, 0.5, 0.75]
        });

        zones.forEach((zone) => observer.observe(zone));
    }
};

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initVeyluneEcosystem, { once: true });
} else {
    initVeyluneEcosystem();
}
