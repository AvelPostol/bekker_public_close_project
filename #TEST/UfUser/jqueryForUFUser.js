console.log('check me-&gt;');
var lio = BX.message('USER_ID');
var arrUs = ['570', '574', '575', '573', '31', '3', '4', '7', '13'];
if (!arrUs.includes(lio)) {
    setTimeout(findAndClickElement, 200);
}

function findAndClickElement() {
    var elements = document.querySelectorAll('.ui-entity-editor-content-block');
    var targetCid = 'UF_CRM_1694018792723';
    if (elements.length > 0) {
        console.log('check me-');
        elements.forEach(function (element) {
            // Get the value of the data-cid attribute for the current element
            var cidValue = element.getAttribute('data-cid');
            // Check if the value of the data-cid attribute matches the target value
            if (cidValue === targetCid) {
                // If yes, add the 'blocked' class for styling
                element.classList.add('blocked');
            }
        });
        setTimeout(findAndClickElement, 2000);
    } else {
        setTimeout(findAndClickElement, 200);
    }
}

/* function sayHi() {
   alert('Привет');
 }
 setTimeout(sayHi, 15000);*/