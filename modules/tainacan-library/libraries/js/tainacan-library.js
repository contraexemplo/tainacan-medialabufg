function showModalImportMarc() {
    $("#modalImportMarc").modal("show");
}

function createMarcItem()
{
    var text = $("#textmarc").val();
    if(text.length > 0)
    {
        send_ajax(text);
    }else
    {
        var file = document.getElementById("inputmarc");

        var fr = new FileReader();
        fr.readAsText(file.files[0]);
        fr.onload = function(e)
        {
            send_ajax(fr.result);
        }
    }
}

function send_ajax($marc)
{
    var url_to_send = $('#src').val() + '/controllers/collection/collection_controller.php?operation=import_marc';
    $.ajax({
        url: url_to_send,
        type: 'POST',
        data: {marc: $marc, collection_id: $("#collection_id").val()},
        beforeSend: function () {
            $("#modalImportMarc").modal("hide");
            $("#modalImportLoading").modal("show");
            $('#progressbarmapas').remove();
        },
        success: function (elem) {
            elem = JSON.parse(elem);
            if(elem.result)
            {
                window.location = elem.url;
            }
        }
    });
}

function save_mapping_marc(){
    $("#mapping_marc").submit(function (event) {
        event.preventDefault();

        $('#modalImportLoading').modal('show');
        $('#progressbarmapas').remove();
        var formData = new FormData(this);
        $.ajax({
            url: $('#src').val() + '/controllers/collection/collection_controller.php',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function (r) {
                /*for(var pair of formData.entries()) {
                    console.log(pair[0]+ ', '+ pair[1]);
                }*/
                var elem = JSON.parse(r);
                if(elem.result)
                {
                    window.location = elem.url;
                }
            }
        });
    });
}
function dataTable()
{
    $("#table-users").DataTable({
        "order": [[ 0, "asc" ]],
        "language": {
            "lengthMenu": "Mostrar _MENU_ registros por página",
            "zeroRecords": "Nada encontrado",
            "info": "Mostrando _PAGE_ de _PAGES_",
            "infoEmpty": "Nenhum registro diponível",
            "infoFiltered": "(de um total de _MAX_ registros)",
            "search": "Busca: ",
            "paginate": {
                "first":      "Primeira",
                "last":       "Última",
                "next":       "Próxima",
                "previous":   "Anterior"
            }
        }
    } );
}

function showUser(userID) {
    var send_url = $('#src').val() + "/modules/tainacan-library/controllers/user_controller.php";
    $("#elemenID").attr("value", userID);
    $.ajax({
        type: 'POST',
        url: send_url,
        data: {operation: 'get_user', user_id: userID}
    }).done(function(res){
        $("#modalShowUser").modal("show");
        $("#user_info").html(res).show();

    })
}

function search_for_users() {
    var user_name = $("#text_box_search").val();
    var send_url = $('#src').val() + "/modules/tainacan-library/controllers/user_controller.php?operation=search_for_user";
    $.ajax({
        type: 'POST',
        url: send_url,
        data: {user_name: user_name},
        success: function (result) {

            $("#where_to_show_users").show();
            $("#users_found").html(result);
        }
    });
}

function verify_enter(e, button_click_id)
{
    if(e.keyCode)
        code = e.keyCode;
    else if(e.which)
        code = e.which;

    if(code == 13)
    {
        $("#"+button_click_id).click();
    }
        
}

function update_user_info()
{
    var send_url = $('#src').val() + "/modules/tainacan-library/controllers/user_controller.php?operation=update_user_info";

    var formData = new FormData($("#editUser")[0]);

    var elemenID = $("#elemenID").val();
    formData.append('elemenID', elemenID);

    $.ajax({
        type: 'POST',
        url: send_url,
        data: formData,
        processData: false,
        contentType: false,
        success: function (r) {
            /*for(var pair of formData.entries()) {
                console.log(pair[0]+ ', '+ pair[1]);
            }*/
            get_users_page('http://localhost/wordpress/biblioteca/wp-content/themes/tainacan', 'show_all_users');
            $("#modalShowUser").modal("hide");
        }
    })
}

function get_users_page(src, op) {
    var send_ctrl = 'user/user_controller.php';
    var send_url = src + '/controllers/' + send_ctrl;
    var send_data = { operation: op };

    $.ajax({ type: 'POST', url: send_url, data: send_data })
        .done(function(res){
            resetHomeStyleSettingsLibrary();
            $('#tainacan-breadcrumbs').hide();
            $('#users_div').html(res).show();
            dataTable();
        })
}

function resetHomeStyleSettingsLibrary() {
    //cl('Entering _resetHomeStyleSettings');
    $('ul.menu-ibram').show();
    $('.ibram-home-container').hide();

    if( $('body').hasClass('page-template-page-statistics') ) {
        $("#tainacan-stats").hide();
        $("#users_div").css('margin-top', '30px');
    } else {
        var $_main = '#main_part';
        $("#users_div").css('margin-top', '0px');
        if( $($_main).hasClass('home') ) {
            $($_main).show().css('padding-bottom', '0%');
            $('#display_view_main_page').hide();
            $('body.home').css('background', 'white');
            $("#searchBoxIndex").hide();
            $('.repository-sharings').css('display', 'block');

        } else {

            //$("#collection_post").css('margin-top', '0').show();
            $("#main_part_collection").show();
            $('.collection_header').hide();
            $($_main).hide();
        }
    }

    $("#configuration").hide();
    var ibram_active = $('.ibram_menu_active').val();
    if( ibram_active && ibram_active == true.toString() ) {
        $('#collection_post').show();
    }
}

