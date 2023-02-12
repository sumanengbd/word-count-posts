(function ($) {

    const windowHeight = window.innerHeight;
    const documentHeight = document.body.offsetHeight;
    const maximum = documentHeight - windowHeight;
    const wcpPbar = document.querySelector(".wcp-progress-bar");
    const wcpPbarWrap = document.querySelector(".wcp-progress-wrap");

    if ( typeof(wcpPbarWrap) !== 'undefined' && wcpPbarWrap !== null ) {
        if ( wcpPbarWrap.hasAttribute("position-custom") && wcpPbarWrap.getAttribute("position") === '0' ) {
            document.querySelector(wcpPbarWrap.getAttribute("position-custom")).prepend(wcpPbarWrap);
        } else if ( wcpPbarWrap.hasAttribute("position-custom") && wcpPbarWrap.getAttribute("position") === '1' ) {
            document.querySelector(wcpPbarWrap.getAttribute("position-custom")).appendChild(wcpPbarWrap);
        }

        wcpPbar.setAttribute("value", (window.scrollY / maximum) * 100);
        
        document.addEventListener("scroll", () => {
            let width = (window.scrollY / maximum) * 100;
            width = width > 100 ? 100 : width;
            wcpPbar.style.width = `${width}%`;
        });
    }
          
}(jQuery));