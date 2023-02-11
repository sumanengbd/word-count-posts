(function ($) {
    $(".wcp-select2").select2({
        width: '100%',
        allowClear: true,
        minimumResultsForSearch: Infinity,
    });

    $(document).ready(function(){
        $('.wcp_color_picker').wpColorPicker();
    });

    // Form Submit
    const submitButton = document.getElementById('wcp-form-submit-button');
    const form = document.getElementById('wcp-form');

    if ( typeof(form) !== 'undefined' && form !== null ) {
        submitButton.addEventListener('click', () => {
            HTMLFormElement.prototype.submit.call(form);
        });
    }

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

    if ( typeof(form) !== 'undefined' && form !== null ) {
        if ( currentTab ) {
            document.querySelector('.wcp-tab__nav a[href="' + currentTab + '"]').click();
        } else {
            document.querySelector('.wcp-tab__nav a:first-child').click();
        }
    }

    // Layout Check
    const checkbox_progress = document.querySelector('input[name="wcp_progress_bar"]');
    const progressBarLocation = document.querySelector('select[name="wcp_progress_bar_location"]');

    if ( typeof(checkbox_progress) !== 'undefined' && checkbox_progress !== null ) {
        window.addEventListener("load", () => {
            // Progress Bar Check
            if (checkbox_progress.checked) {
                const parent = checkbox_progress.closest("tr");
                const siblings = [...parent.parentElement.children].filter(
                    tr => tr !== parent && !tr.querySelector('[name="wcp_progress_bar"]')
                );
        
                siblings.forEach(tr => {
                    tr.style.display = "table-row";
                });
            }

            checkbox_progress.addEventListener('change', () => {
                const parent = checkbox_progress.closest('tr');
                const siblings = [...parent.parentElement.children].filter(tr => tr !== parent && !tr.querySelector('[name=wcp_progress_bar]'));

                if ( checkbox_progress.checked) {
                    siblings.forEach(tr => tr.style.display = 'table-row');
                } else {
                    siblings.forEach(tr => tr.style.display = 'none');
                }

                if ( progressBarLocation.value !== '2' ) {
                    const siblingTr = [...parentTr.parentElement.children].filter(tr => tr !== parentTr && !tr.querySelector('[name=wcp_progress_bar],[name=wcp_progress_bar_thickness]') && !tr.querySelector('.wp-picker-container'));
                    
                    siblingTr[0].style.display = 'none';
                }
            });

            // Select Custom Location
            
            const parentTr = progressBarLocation.closest('tr');
            const siblingTr = [...parentTr.parentElement.children].filter(tr => tr !== parentTr && !tr.querySelector('[name=wcp_progress_bar],[name=wcp_progress_bar_thickness]') && !tr.querySelector('.wp-picker-container'));
            if ( progressBarLocation.value == '2' ) {
                siblingTr[0].style.display = 'table-row';
            } else {
                siblingTr[0].style.display = 'none';
            }
        
            progressBarLocation.addEventListener('change', () => {
                console.log(siblingTr);
                if ( progressBarLocation.value == '2' ) {
                    siblingTr[0].style.display = 'table-row';
                } else {
                    siblingTr[0].style.display = 'none';
                }
            });
        });
    }

}(jQuery));