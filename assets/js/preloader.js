window.addEventListener("load", function () {

    const loader = document.getElementById("preloader");

    if (!loader) return;

    document.body.style.overflow = "hidden";

    setTimeout(function () {

        loader.classList.add("hide");

        document.body.style.overflow = "";

        setTimeout(function () {

            loader.remove();

        }, 500);

    }, 1200);

});