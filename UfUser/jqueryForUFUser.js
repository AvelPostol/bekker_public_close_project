document.addEventListener('DOMContentLoaded', function() {
    const lio = BX.message('USER_ID');
    const arrUsPrimary = ['570', '574', '575', '573', '31', '3', '4', '7', '13'];
    const arrUsSecondary = ['38', '3', '8887'];
    const specialUserId = '8887';
    const targetCidPrimary = 'UF_CRM_1694018792723';
    const targetCidsSecondary = ['UF_CRM_BKK_SHTRAF', 'UF_CRM_BKK_DATAPROVER'];

    function processNode(node, targetCids, exclude = false) {
        const dataCid = node.getAttribute('data-cid');
        // Проверяем, что у элемента есть атрибут data-cid
        if (dataCid && node.nodeType === 1 && ((exclude && !targetCids.includes(dataCid) && dataCid !== 'main' && !dataCid.startsWith('user_')) || (!exclude && targetCids.includes(dataCid)))) {
            console.log('Target element found, modifying:', dataCid);
            node.classList.add('blocked');
        }
    }

    function processExistingElements() {
        document.querySelectorAll('[data-cid]').forEach(node => {
            const dataCid = node.getAttribute('data-cid');
            if (lio === specialUserId && dataCid !== 'main' && !dataCid.startsWith('user_')) {
                processNode(node, targetCidsSecondary, true);
            } else if (!arrUsPrimary.includes(lio) && dataCid === targetCidPrimary) {
                processNode(node, [targetCidPrimary]);
            } else if (!arrUsSecondary.includes(lio) && targetCidsSecondary.includes(dataCid)) {
                processNode(node, targetCidsSecondary);
            }
        });
    }

    processExistingElements();

    const observer = new MutationObserver((mutations) => {
        mutations.forEach((mutation) => {
            mutation.addedNodes.forEach((node) => {
                if (node.nodeType === 1 && node.getAttribute('data-cid')) {
                    if (lio === specialUserId && node.getAttribute('data-cid') !== 'main' && !node.getAttribute('data-cid').startsWith('user_')) {
                        processNode(node, targetCidsSecondary, true);
                    }
                }
            });
        });
    });

    const config = {
        childList: true,
        subtree: true,
        attributes: false,
    };

    if (document.body) {
        observer.observe(document.body, config);
    }
});
