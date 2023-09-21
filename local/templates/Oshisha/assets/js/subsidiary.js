BX.ready(
    function () {
        BX.bind(BX('subsidiary_link'), 'change', function (event) {
            
            BX.ajax({
                url: '/local/ajax/subsidiary.php',
                method: 'POST',
                data: {subsidiary: event.target.value},
                onsuccess: function (response) {
                    if (response === 'success') {
                        location.reload()
                    }
                }
            })
        })
    }
)