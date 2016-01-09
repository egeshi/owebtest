$(function(){

    var formId = "#fileupload";
    var $form = $(formId);
    var $inputs = $form.find('input[type="file"]');
    var $uploadBtn = $('#uploadBtn');

    $('input[type="file"]').change(function(evt){

        var hasFile = 0;

        $inputs.each(function(){
            if ($(this).val()) {
                var fileName = $(this).val().match(/^.+(\/|\\)(.+)$/)[2];
                hasFile++;
            }
        });

        if (hasFile >= 2) {
            $uploadBtn.attr({disabled: false});

            if (!document.getElementById('addMoreBtn')) {
                $form.append($('<button/>').attr({
                    class: "btn btn-success",
                    type: "button",
                    id: 'addMoreBtn'
                }).html("Add more"));
                
                $('#pleaseSelect').html('Click "Add more" button to load more files');
                
                $('#addMoreBtn').click(function(evt){
                    $form.find(".controls").append($('<input/>').attr({
                        type: "file",
                        name: "files[]"}));
                });
            }
        }
    });

    $uploadBtn.click(function(evt){
        var data = new FormData();
        $('input[type="file"]').each(function(idx, item){
            data.append("files[]", item.files[0]);
        });

        $.ajax({
            url: $form.attr("action"),
            method: $form.attr("method"),
            data: data,
            cache: false,
            processData: false,
            contentType: false,
            dataType: "json",
            error: function(data, status, error){
                $('#response').html(data.responseText);
            },
            success: function(data){
                $('#response').html(data);
            },
            beforeSend: function(){
                $('#response').html("");
            }

        });

    });
});