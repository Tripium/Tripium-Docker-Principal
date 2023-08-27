(function ($) {
    const $msg = $('#wiloke-static-files-generation-log');
    const $performStaticFiles = $('#wilcity-generate-static-files');
    const $cancelStaticFiles = $('#wilcity-cancel-generate-static-files');
    let originalPerformBtnName = '';
    let xhrGenerateStaticFiles = null;
    let xhrCancelGenerateStaticFiles = null;

    $(document).ready(function () {
        $performStaticFiles.on('click', function (event) {
            $msg.html('');
            event.preventDefault();
            originalPerformBtnName = $performStaticFiles.html();
            $performStaticFiles.html('Setting up');
            $cancelStaticFiles.removeClass('disabled');

            generateStaticFiles();
        });

        $cancelStaticFiles.on('click', function (event) {
            event.preventDefault();
            $performStaticFiles.html(originalPerformBtnName);
            xhrGenerateStaticFiles.abort();

            xhrCancelGenerateStaticFiles = jQuery.ajax({
                type: 'POST',
                url: ajaxurl,
                data: {
                    action: 'cancel_generate_static_files'
                },
                success: (response => {
                    alert(response.data.msg);
                    $cancelStaticFiles.addClass('disabled');
                })
            })
        })
    })

    function generateStaticFiles(currentTask) {
        xhrGenerateStaticFiles = jQuery.ajax({
            type: 'POST',
            url: ajaxurl,
            data: {
                action: 'generate_static_files',
                currentTask
            },
            success: (response => {
                if (response.success) {
                    currentTask = response.data.nextTask;
                    if (currentTask !== 'almost_done' || currentTask === 'done') {
                        if (currentTask === 'waiting') {
                            setTimeout(() => {
                                generateStaticFiles(currentTask);
                            }, 1000);
                        } else {
                            generateStaticFiles(currentTask);
                        }
                    } else {
                        $performStaticFiles.html(originalPerformBtnName);
                        $cancelStaticFiles.addClass('disabled');
                    }
                    $msg.append(`<p>${response.data.msg}</p>`);
                } else {
                    alert(response.data.msg);
                    $cancelStaticFiles.addClass('disabled');
                }
            })
        }).fail( response => {
            if (xhrCancelGenerateStaticFiles === null) {
                alert('Something went wrong');
            }
        });
    }
})(jQuery);
