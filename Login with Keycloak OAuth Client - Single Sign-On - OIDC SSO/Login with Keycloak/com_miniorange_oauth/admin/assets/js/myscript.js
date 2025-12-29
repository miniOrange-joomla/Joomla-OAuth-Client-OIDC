function add_css_tab(element) {
    jQuery(".mo_nav_tab_active").removeClass("mo_nav_tab_active").removeClass("active");
    jQuery(element).addClass("mo_nav_tab_active");
}

function copyToClipboard1(element) {
    var temp = jQuery("<input>");
    jQuery("body").append(temp);
    temp.val(jQuery(element).val()).select();
    document.execCommand("copy");
    temp.remove();
}

function copyToClipboard(element1, element2) {
    var temp = jQuery("<input>");
    jQuery("body").append(temp);
    $value = jQuery(element2).val() + jQuery(element1).val();
    temp.val($value).select();
    document.execCommand("copy");
    temp.remove();
}

function validateEmail(emailField) {
    var reg = /^([A-Za-z0-9_\-\.])+\@([A-Za-z0-9_\-\.])+\.([A-Za-z]{2,4})$/;
    if (reg.test(emailField.value) == false) {
        document.getElementById('email_error').style.display = "block";
        document.getElementById('submit_button').disabled = true;
    }
    else {
        document.getElementById('email_error').style.display = "none";
        document.getElementById('submit_button').disabled = false;
    }
}

jQuery(document).ready(function () {
    jQuery('.premium').click(function () {
        jQuery('.nav-tabs a[href=#licensing-plans]').tab('show');
    });
});

function upgradeBtn() {
    jQuery("#myModal").css("display", "block");
}
function upgradeClose() {
    jQuery("#myModal").css("display", "none");
}
function oauth_back_to_register() {
    jQuery('#oauth_cancel_form').submit();
}

function mo_oauth_show_proxy_form() {
    jQuery('#submit_proxy1').show();
    jQuery('#register_with_miniorange').hide();
    jQuery('#proxy_setup1').hide();
}

function mo_oauth_hide_proxy_form() {
    jQuery('#submit_proxy1').hide();
    jQuery('#register_with_miniorange').show();
    jQuery('#proxy_setup1').show();
    jQuery('#submit_proxy2').hide();
    jQuery('#mo_oauth_registered_page').show();
}

function mo_oauth_show_proxy_form2() {
    jQuery('#submit_proxy2').show();
    jQuery('#mo_oauth_registered_page').hide();
}

window.addEventListener('DOMContentLoaded', function () {
    let supportButtons = document.getElementsByClassName('moJoom-OauthClient-supportButton-SideButton');
    let supportForms = document.getElementsByClassName('moJoom-OauthClient-supportForm');
    for (let i = 0; i < supportButtons.length; i++) {
        supportButtons[i].addEventListener("click", function (e) {
            if (supportForms[i].style.right != "0px") {
                supportForms[i].style.right = "0px";
            }
            else {
                supportForms[i].style.right = "-391px";
            }
        });
    }

    let appSearchInput = document.getElementById('moAuthAppsearchInput');
    let moOAuthAppsTable = document.getElementById('moOAuthAppsTable');
    let moOpenIDAppsTable = document.getElementById('moOpenIDConnectAppsTable');
    let originalHtmlOAuth = '';
    let originalHtmlOpenID = '';

    if (moOAuthAppsTable != null)
        originalHtmlOAuth = moOAuthAppsTable.innerHTML;
    if (moOpenIDAppsTable != null)
        originalHtmlOpenID = moOpenIDAppsTable.innerHTML;

    let noAppFoundStr = '<tr><td>No applications found in this category, matching your search query. Please select a Custom Application or <b><a href="#" style="cursor:pointer;text-decoration:none;">Contact Us</a></b></td></tr>';

    if (appSearchInput != null) {
        appSearchInput.addEventListener('input', function () {
            filterTable(moOAuthAppsTable, originalHtmlOAuth);
            filterTable(moOpenIDAppsTable, originalHtmlOpenID);
        });
    }

    function filterTable(tableElement, originalHtml) {
        if (!tableElement) return;

        let allTds = tableElement.querySelectorAll('tr td');
        let query = appSearchInput.value.toLowerCase().trim();
        let htmlStr = '';
        let matchCount = 0;

        for (let i = 0; i < allTds.length; i++) {
            let selector = allTds[i].getAttribute('moAuthAppSelector');
            let cellText = allTds[i].textContent || allTds[i].innerText || '';

            // Check both the selector attribute and the visible text content
            let matchesSelector = selector && selector.toLowerCase().indexOf(query) !== -1;
            let matchesText = cellText.toLowerCase().indexOf(query) !== -1;

            if (matchesSelector || matchesText) {
                if (matchCount % 6 === 0) {
                    htmlStr += '<tr>';
                }
                htmlStr += '<td>' + allTds[i].innerHTML + '</td>';
                matchCount++;
                if (matchCount % 6 === 0) {
                    htmlStr += '</tr>';
                }
            }
        }

        // Close any open row
        if (matchCount % 6 !== 0 && matchCount > 0) {
            htmlStr += '</tr>';
        }

        if (query === '') {
            tableElement.innerHTML = originalHtml;
        } else if (matchCount === 0) {
            tableElement.innerHTML = noAppFoundStr;
        } else {
            tableElement.innerHTML = htmlStr;
        }
    }

    const urlParams = new URLSearchParams(window.location.search);
    const subtab = urlParams.get('subtab');

    if (subtab === 'mo_request_demo') {
        // Directly open the demo request tab
        changeSubMenu('#support', document.querySelector("[onclick*='#mo_request_demo']"), '#mo_request_demo');
    }

});

/*removed internal js*/
function closeModel() {
    jQuery(".TC_modal").css("display", "none");
}
function show_TC_modal() {
    jQuery(".TC_modal").css("display", "block");
}
function callbackURLFormSubmit() {
    jQuery("#oauth_config_form_step1").submit();
}

function changeSubMenu(tabPanelId, element0, element1) {
    var $panel = jQuery(tabPanelId);
    $panel.find('.mo_oauth_sub_menu_active').removeClass('mo_oauth_sub_menu_active');
    jQuery(element0).addClass('mo_oauth_sub_menu_active');
    jQuery(element1).nextAll('div').css('display', 'none');
    jQuery(element1).prevAll().css('display', 'none');
    jQuery(element1).css('display', 'block');
}

jQuery(document).ready(function () {
    var dtToday = new Date();
    var month = dtToday.getMonth() + 1;
    var day = dtToday.getDate();
    var year = dtToday.getFullYear();
    if (month < 10)
        month = '0' + month.toString();
    if (day < 10)
        day = '0' + day.toString();
    var maxDate = year + '-' + month + '-' + day;

    jQuery('#calldate').attr('min', maxDate);
});

function show_TC_modal() {
    jQuery(".TC_modal").css("display", "block");
}

function displayFileName() {
    var fileInput = document.getElementById('fileInput');
    var file = fileInput.files[0];

    if (file && file.name.endsWith('.json')) {
        document.getElementById('fileName').textContent = file.name;
    } else {
        document.getElementById('fileName').textContent = "Please select a .json file.";
    }
}


document.querySelectorAll(".page-btn").forEach(function (btn) {
    btn.addEventListener("click", function () {
        let page = this.getAttribute("data-page");
        document.cookie = "log_page=" + page + "; path=/; max-age=" + (60 * 60 * 24); // 1 day
        let siteBase = window.location.origin + window.location.pathname.split('administrator')[0];
        let targetUrl = siteBase + 'administrator/index.php?option=com_miniorange_oauth&view=accountsetup&tab-panel=loggerreport';
        window.location.href = targetUrl;
    });
});

function toggleFeatureList(headerId) {
    const list = document.getElementById(headerId);
    const arrow = list.previousElementSibling.querySelector(".mo_oauth_feature_arrow i");

    if (list.style.display === "none") {
        list.style.display = "block";
        arrow.classList.remove("fa-chevron-down");
        arrow.classList.add("fa-chevron-up");
    } else {
        list.style.display = "none";
        arrow.classList.remove("fa-chevron-up");
        arrow.classList.add("fa-chevron-down");
    }
}

function toggleCollapse(contentId, iconElement) {
    let content = document.getElementById(contentId);
    if (content.style.display === "none" || content.style.display === "") {
        content.style.display = "block";
        iconElement.textContent = "-";
    } else {
        content.style.display = "none";
        iconElement.textContent = "+";
    }
}
