// Animation des étoiles de la carte profil de l'agent.
// (Les anciens graphiques de démonstration à données aléatoires ont été remplacés
// par le composant <x-gent.DashBoard.performance>, branché sur la base.)

document.addEventListener("DOMContentLoaded", function () {
    const starsContainer = document.querySelector(".stars-container");
    if (!starsContainer) return; // pas de carte profil sur cette page
    const numberOfStars = 5; //nombre d'étoile

    starsContainer.innerHTML = "";

    const fillClasses = [
        "bad-rate",
        "normal3-rate",
        "normal2-rate",
        "normal-rate",
        "good-rate",
    ];

    for (let i = 1; i <= numberOfStars; i++) {
        const starWrapper = document.createElement("div");

        // Attribue une classe spécifique pour chaque étoile
        starWrapper.classList.add(fillClasses[i - 1]);

        const svg = document.createElementNS(
            "http://www.w3.org/2000/svg",
            "svg"
        );
        svg.setAttribute("viewBox", "0 0 24 24");
        const use = document.createElementNS(
            "http://www.w3.org/2000/svg",
            "use"
        );
        use.setAttribute("href", "#star");
        use.classList.add("icon-star");

        svg.appendChild(use);
        starWrapper.appendChild(svg);
        starsContainer.appendChild(starWrapper);
    }

    switch (numberOfStars) {
        case 3:
            starsContainer.classList.add("stars-3");
            break;
        case 4:
            starsContainer.classList.add("stars-4");
            break;
        case 5:
            starsContainer.classList.add("stars-5");
            break;
    }

    setTimeout(() => {
        starsContainer.classList.add("animated");
    }, 1500);
});
