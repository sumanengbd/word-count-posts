(function ($) {

    window.addEventListener("load", () => {
        const windowHeight = window.innerHeight;
        const documentHeight = document.body.offsetHeight;
        const maximum = documentHeight - windowHeight;
        const wcpPbar = document.querySelector(".wcp-progress-bar");
        const wcpPbarWrap = document.querySelector(".wcp-progress-wrap");

        if (wcpPbarWrap.hasAttribute("position-custom")) {
            document.querySelector(wcpPbarWrap.getAttribute("position-custom")).appendChild(wcpPbarWrap);
        }
        
        wcpPbar.setAttribute("value", (window.scrollY / maximum) * 100);
        
        document.addEventListener("scroll", () => {
            let width = (window.scrollY / maximum) * 100;
            width = width > 100 ? 100 : width;
            wcpPbar.style.width = `${width}%`;
        });
    });  
          
}(jQuery));