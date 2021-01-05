   
require(['jquery', 'jqueryui','core/modal_factory'], function($, ModalFactory){

    // previewbutton - open modal.
    $('#id_previewbutton').click(function(){
        
        $("#preview").remove();
        $("#notvalidmessage").remove();

        var i;
        var count = $('#partcount').val();
        var splitcmid = $('#splitcmid').val();
        
        
        var i;
        var notvalid = 0;

        for (i = 1; i < count; i++) {
            part = $('#id_part-' + i + '').val();
            if (part % 1 != 0 || part < 0) {
                notvalid = 1;
            }  
            if (i != 1) {
                breakk = $('#id_break-' + i + '').val();
                if (breakk % 1 != 0 || part < 0) {
                    notvalid = 1;
                } 
            } 
        }

        part1 = $('#id_part-1').val();
        part2 = $('#id_part-2').val();
        part3 = $('#id_part-3').val();
        part4 = $('#id_part-4').val();
        part5 = $('#id_part-5').val();
        part6 = $('#id_part-6').val();
        part7 = $('#id_part-7').val();
        part8 = $('#id_part-8').val();

        break2 = $('#id_break-2').val();
        break3 = $('#id_break-3').val();
        break4 = $('#id_break-4').val();
        break5 = $('#id_break-5').val();
        break6 = $('#id_break-6').val();
        break7 = $('#id_break-7').val();
        break8 = $('#id_break-8').val();

        if (notvalid == 1) {
            notvalidmessage = "יש להזין מספר שלם חיובי";
            $('.mform').append(`<div id='notvalidmessage' class='alert alert-danger'>` + notvalidmessage + `</div>`);
        } else {    
            var url = M.cfg.wwwroot + '/local/split_module/ajaxpreview.php';
            if(!count) {
                    return 0;
            }
            var preview;
            $.ajax({
                    url: url,
                    type: "POST",
                    async: false,
                    dataType: 'Json',
                    data: {
                            "count" : count,
                            "cmid"  : splitcmid,
                            "part1" : part1,
                            "part2" : part2,
                            "part3" : part3,
                            "part4" : part4,
                            "part5" : part5,
                            "part6" : part6,
                            "part7" : part7,
                            "part8" : part8,
                            "break2" : break2,
                            "break3" : break3,
                            "break4" : break4,
                            "break5" : break5,
                            "break6" : break6,
                            "break7" : break7,
                            "break8" : break8,
                    },
                    success: function(data){
                            preview = data;
                            $("#splitpreview").append("<div id='preview'>"+ preview +"</div>");
                            $('#split_mypop-modal-title').closest(".modal").show();
                    },
                    error: function(XMLHttpRequest, textStatus, errorThrown) {
                        alert("Status: " + textStatus); alert("Error: " + errorThrown);
                    }
            });
        }
    });


    $('#id_splitbutton').click(function(){
        
        $( "#message" ).remove();
        var i;
        var count = $('#partcount').val();
        var splitcmid = $('#splitcmid').val();
        sumparts = 0;

        part1 = $('#id_part-1').val();
        part2 = $('#id_part-2').val();
        part3 = $('#id_part-3').val();
        part4 = $('#id_part-4').val();
        part5 = $('#id_part-5').val();
        part6 = $('#id_part-6').val();
        part7 = $('#id_part-7').val();
        part8 = $('#id_part-8').val();

        break2 = $('#id_break-2').val();
        break3 = $('#id_break-3').val();
        break4 = $('#id_break-4').val();
        break5 = $('#id_break-5').val();
        break6 = $('#id_break-6').val();
        break7 = $('#id_break-7').val();
        break8 = $('#id_break-8').val();

        var url = M.cfg.wwwroot + '/local/split_module/ajaxsplit.php';
        if(!count) {
                return 0;
        }
        var message = "";
        $.ajax({
                url: url,
                type: "POST",
                async: false,
                dataType: 'Json',
                data: {
                        "count" : count,
                        "cmid"  : splitcmid,
                        "part1" : part1,
                        "part2" : part2,
                        "part3" : part3,
                        "part4" : part4,
                        "part5" : part5,
                        "part6" : part6,
                        "part7" : part7,
                        "part8" : part8,
                        "break2" : break2,
                        "break3" : break3,
                        "break4" : break4,
                        "break5" : break5,
                        "break6" : break6,
                        "break7" : break7,
                        "break8" : break8,
 
                },
                success: function(data){
                    message = data;
                    if (message == "") {
                        status  = true;
                    } else {
                        $('.mform').append(`<div id='message' class='alert alert-danger'>` + message + `</div>`); 
                        status  = false;
                    }
                },
                error: function(XMLHttpRequest, textStatus, errorThrown) {
                    alert("Status: " + textStatus); alert("Error: " + errorThrown);
                }
        });
        if (status == "false") {
            return false; 
        }
        return true; 
    });


    $(".modal .close").click(function() {
        $('#split_mypop-modal-title').closest(".modal").hide();
     });

     $('.closebtn').click(function(){
        $('#split_mypop-modal-title').closest(".modal").hide();
    });

    // Addpart button 
    $('#id_addpartbutton').on('click', function() {
       
        var count = parseInt($('#partcount').val());
        $('#fitem_id_break-' + count).removeClass("local_split_hiddenpart");
        $('#fitem_id_part-' + count).removeClass("local_split_hiddenpart");
        count = count +1;
        $('#partcount').val(count);
       
    });
});