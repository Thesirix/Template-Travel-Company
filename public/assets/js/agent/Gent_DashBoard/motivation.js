document.addEventListener("DOMContentLoaded", function () {
    // La modale n'est rendue qu'une fois par jour (session showMotivationMessage)
    if (!document.getElementById("motivationalMessage")) return;
    let intros = [
        "🔥 Aujourd'hui est un grand jour !",
        "🌞 Une nouvelle journée commence !",
        "💪 Tu es plus fort que tu ne le penses !",
        "🚀 C'est le moment de briller !",
        "✨ Chaque matin est une opportunité !",
        "🏆 Rien ne peut t’arrêter aujourd’hui !",
        "💥 Prêt à conquérir ta journée ?",
        "⚡ L’énergie est avec toi !",
        "🔥 Ton potentiel est illimité !",
        "🌟 Un pas de plus vers le succès !",
        "🎯 Tout commence ici et maintenant !",
        "⏳ Ne perds pas une seconde, avance !",
        "💡 Brille comme jamais aujourd’hui !",
        "🛠️ Construis ton futur dès maintenant !",
        "⛰️ Atteins de nouveaux sommets !",
        "🌈 La réussite t’attend au bout du chemin !",
        "💥 Aujourd’hui, sois la meilleure version de toi-même !",
        "🌍 Fais vibrer le monde avec ton talent !",
        "🚀 Décolle vers tes rêves !",
        "⚡ Le temps est venu de passer à l’action !",
    ];

    let actions = [
        "Saisis chaque opportunité !",
        "Transforme les défis en succès !",
        "N'abandonne jamais, la persévérance paie !",
        "Repousse tes limites aujourd’hui !",
        "Crée ton propre chemin vers la réussite !",
        "Continue à apprendre et évoluer !",
        "Fais un pas en avant, chaque jour !",
        "Travaille dur et ne lâche rien !",
        "Sois audacieux, ose prendre des risques !",
        "Sois la personne qui inspire les autres !",
        "Fais de ton mieux, encore et encore !",
        "Prends le contrôle de ton destin !",
        "Passe à l'action, même si c'est imparfait !",
        "Défie tes doutes et avance !",
        "Affronte tes peurs et dépasse-les !",
        "Reste concentré sur ton objectif !",
        "Chaque petit progrès compte !",
        "Célèbre chaque victoire, même petite !",
        "Persévère, même quand c’est dur !",
        "Fais aujourd’hui ce que les autres n’osent pas !",
    ];

    let ends = [
        "Le succès t’attend 🚀",
        "Rien ne peut t’arrêter ! 💪",
        "L’avenir est à toi ! 🌟",
        "Chaque pas te rapproche de ton rêve 🎯",
        "Ne laisse rien freiner ton ascension 🔥",
        "Tu es né pour accomplir de grandes choses 👑",
        "Ta réussite est inévitable 📈",
        "Ce n’est que le début de ton succès 🏆",
        "Tout est possible pour celui qui ose ✨",
        "Sois fier de chaque effort fourni 🛠️",
        "Les résultats viendront, continue d’y croire 💡",
        "Tes rêves deviennent réalité à chaque instant 🎉",
        "Tu construis un futur brillant 🚦",
        "Les champions ne s’arrêtent jamais 🥇",
        "Le monde a besoin de ton talent 🌍",
        "Avance avec détermination et fierté ⚡",
        "Ne te limite pas, tout est entre tes mains 💥",
        "Rappelle-toi pourquoi tu as commencé 🔄",
        "Un jour, tu regarderas en arrière et tu seras fier 🤩",
        "Garde le cap, la victoire est proche 🏁",
    ];

    let emojis = [
        "🚀",
        "🔥",
        "💡",
        "🌟",
        "🎯",
        "🏆",
        "💪",
        "✨",
        "⚡",
        "🎉",
        "😎",
        "🛠️",
        "🌍",
        "🧗‍♂️",
        "💥",
        "🚦",
        "👑",
        "📈",
        "🕶️",
        "🤩",
    ];

    function getRandomElement(array) {
        return array[Math.floor(Math.random() * array.length)];
    }

    let message = `${getRandomElement(intros)} ${getRandomElement(
        actions
    )} ${getRandomElement(ends)} ${getRandomElement(emojis)}`;
    let messageElement = document.getElementById("motivationalMessage");
    messageElement.innerHTML = "";

    // Les lettres arrivent en désordre puis se remettent en place : le message reste lisible.
    // Découpage par caractère réel (emojis composés compris) et mot par mot (pas de coupure en fin de ligne).
    const segmenter = window.Intl && Intl.Segmenter ? new Intl.Segmenter("fr", { granularity: "grapheme" }) : null;
    const characters = (text) => (segmenter ? [...segmenter.segment(text)].map((s) => s.segment) : Array.from(text));
    let index = 0;
    message.split(" ").forEach((word, w) => {
        if (w > 0) messageElement.appendChild(document.createTextNode(" "));
        const wordSpan = document.createElement("span");
        wordSpan.style.whiteSpace = "nowrap";
        messageElement.appendChild(wordSpan);
        characters(word).forEach((letter) => {
            const span = document.createElement("span");
            span.textContent = letter;
            span.style.display = "inline-block";
            wordSpan.appendChild(span);
            gsap.from(span, {
                opacity: 0,
                x: gsap.utils.random(-14, 14),
                y: gsap.utils.random(-14, 14),
                rotation: gsap.utils.random(-30, 30),
                duration: 0.5,
                delay: 0.3 + index++ * 0.02,
                ease: "back.out(1.7)",
            });
        });
    });

    $("#motivationModal").modal("show");
});
