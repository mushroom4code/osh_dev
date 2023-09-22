BX.ready(
    function () {
        const boxSelect =  $('#subsidiary_link');
        if (boxSelect.length > 0) {
            $(boxSelect).select2({
                minimumResultsForSearch: -1,
            });
        }
        $(boxSelect).on('select2:select',function(event){
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