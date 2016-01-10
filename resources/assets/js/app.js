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
            var addmoreText = "More files";
            if (!document.getElementById('addMoreBtn')) {
                $form.find('.buttonsContainer .buttons')
                        .append($('<button/>').attr({
                            class: "btn btn-success",
                            type: "button",
                            id: 'addMoreBtn'
                        }).html(addmoreText));

                $('#pleaseSelect').html('Click ' + addmoreText + ' button to load more files');

                $('#addMoreBtn').click(function(evt){
                    var $tmpl = $form.find('.form-group').slice(0, -1).last().clone();
                    var idx = parseInt($tmpl.find('input').attr('id').match(/\d+$/)[0]) + 1;
                    $tmpl.find('label').attr({'for': 'file' + idx}).html('File ' + idx);
                    $tmpl.find('input').attr({'id': 'file' + idx});
                    var $c = $form.find(".buttonsContainer");
                    $tmpl.insertBefore($c);
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