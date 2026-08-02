const counters = document.querySelectorAll(".counter");

const observer = new IntersectionObserver((entries) => {

    entries.forEach(entry => {

        if(!entry.isIntersecting) return;

        const counter = entry.target;

        const target = Number(counter.dataset.target);

        let current = 0;

        const increment = Math.max(1, Math.ceil(target / 80));

        const update = () => {

            current += increment;

            if(current >= target){

                counter.textContent = target.toLocaleString();

                return;

            }

            counter.textContent = current.toLocaleString();

            requestAnimationFrame(update);

        };

        update();

        observer.unobserve(counter);

    });

});

counters.forEach(counter => observer.observe(counter));