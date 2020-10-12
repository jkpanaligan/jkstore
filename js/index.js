window.addEventListener('DOMContentLoaded', () => {
    const ham = document.querySelector('#ham');
    const menus = document.querySelector('#menus');

    ham.addEventListener('click', () => {
        if(menus.className.includes("show")){
            menus.classList.remove("show");
        }else{
            menus.classList.add("show");
        }
    });
});

window.addEventListener("resize", () => {
    if(window.innerWidth > 992){
        if(menus.className.includes("show")){
            menus.classList.remove("show");
        }
    }
});

