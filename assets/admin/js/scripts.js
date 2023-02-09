(function ($) {
    $(".wcp-select2").select2({
        width: '100%',
        allowClear: true,
        minimumResultsForSearch: Infinity,
    });

    document.getElementById("wcp-settings-form-submit").addEventListener("click", function() {
        document.getElementById("wcp-settings-form").submit();
    });

    // Tab JavaScript code
    var tabNavLinks = document.querySelectorAll('.wcp-tab__nav a');
    var currentTab = localStorage.getItem('currentTab');
    
    for (var i = 0; i < tabNavLinks.length; i++) {
        tabNavLinks[i].addEventListener('click', function (e) {
            e.preventDefault();
            var currentTabNavLink = this;
            var currentTabNavParent = currentTabNavLink.parentNode;
            currentTabNavParent.classList.add('active');
            var tabNavSiblings = currentTabNavParent.parentNode.children;
            for (var j = 0; j < tabNavSiblings.length; j++) {
                if (tabNavSiblings[j] !== currentTabNavParent) {
                    tabNavSiblings[j].classList.remove('active');
                }
            }

            var targetId = currentTabNavLink.getAttribute('href');
            localStorage.setItem('currentTab', targetId);
            var tabContents = document.querySelectorAll('.wcp-tab__content');

            for (var k = 0; k < tabContents.length; k++) {
                if (tabContents[k].id !== targetId.substring(1)) {
                    tabContents[k].style.display = 'none';
                } else {
                    tabContents[k].style.display = 'block';
                }
            }
        });
    }

    if (currentTab) {
        document.querySelector('.wcp-tab__nav a[href="' + currentTab + '"]').click();
    } else {
        document.querySelector('.wcp-tab__nav a:first-child').click();
    }

}(jQuery));