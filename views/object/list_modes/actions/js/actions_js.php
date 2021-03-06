<script type="text/javascript">
    $(function() {
        var path = $("#src").val() + '/controllers/object/object_controller.php';
        var _col_id = $("#collection_id").val();

        $('.ac-duplicate-item').on('click', function() {
            var item_id = $(this).parents().find('.open_item_actions').first().attr('id').replace('action-', '');
            var duplicate_op = $(this).attr('data-op');
            var op = 'duplicate_item_' + duplicate_op + '_collection';
            var send_data = { object_id: item_id, operation: op, collection_id: $("#collection_id").val() };

            if("other" == duplicate_op) {
                send_data.collection_id = _col_id;
                show_duplicate_item(item_id);
                var current_item = $.trim($("#object_" + item_id + " .item-display-title").text());
                var dup_text = '<?php _t("Duplicate "); ?>' + current_item + '<?php _t(" at another collection")?>';
                $("#modal_duplicate_object" + item_id + " .modal-title").text( dup_text );
                $("#modal_duplicate_object" + item_id + " br").remove();
                $("#modal_duplicate_object" + item_id + " input[type=radio]").hide().get(1).click();
                $("#modal_duplicate_object" + item_id + " label").hide();
                $("#modal_duplicate_object" + item_id + " label.other_collection").show().text('<?php _t("Search collection",1); ?>');
            } else if("same" == duplicate_op) {
                $('#modalImportMain').modal('show');
                $.ajax({
                    type: 'POST', url: path,
                    data: send_data
                }).done(function(r){
                    $('#modalImportMain').modal('hide');
                    location.reload();
                });
            }
        });

        $('.ac-create-version').on('click', function() {
            var item_id = $(this).parents().find('.open_item_actions').first().attr('id').replace('action-', '');
            var current_item = $.trim($("#object_" + item_id + " .item-display-title").text());
            var modal_text = '<?php _t("Create new version of "); ?>' + current_item;

            $('#modal_duplicate_object' + item_id).modal('show').find('br').remove();
            $("#modal_duplicate_object" + item_id + " .modal-title").text( modal_text );
            $("#modal_duplicate_object" + item_id + " input[type=radio]").hide().get(2).click();
            $("#modal_duplicate_object" + item_id + " label").hide();
            $("#modal_duplicate_object" + item_id + " label.version").show().text('< ?php _t("Versioning",1); ?>');
        });

        $('a.ac-item-versions').on('click', function() {
            var item_id = $(this).parents().find('.open_item_actions').first().attr('id').replace('action-', '');
            show_modal_main();
            $.ajax({
                type: 'POST', url: path,
                data: {operation: 'show_item_versions', object_id: item_id, collection_id: _col_id}
            }).done(function(r) {
                hide_modal_main();
                $('#main_part').hide();
                $('#tainacan-breadcrumbs').hide();
                $('#configuration').html(r).show();
            });
        });

        $('a.ac-comment-item').on('click', function() {
            var item_id = $(this).parents().find('.open_item_actions').first().attr('id').replace('action-', '');
            $.ajax({
                type: 'POST', url: path,
                data: {collection_id: $('#collection_id').val(), operation: 'list_comments', object_id: item_id}
            }).done(function(r){
                $("#comment_item"+item_id + ' .modal-body').html(r);
                $("#comment_item"+item_id).modal('show');
            });

        });

        $("#change_item_file").click(function(){
            $("#change_item_file_modal").modal("show");

        });

        $("#new_item_file").submit(function (event) {
            event.preventDefault();

            if( document.getElementById("new_file").files.length === 0 ){
                swal("<?php _t("No file selected", "tainacan")?>", "<?php _t("You need to select a file before proceed", "tainacan");?>", "info");
            }else
            {
                let name = document.getElementById("new_file").files[0].name;
                let ext = name.split('.');
                ext = ext[ext.length - 1].toLowerCase();

                let data =  new FormData(this);
                data.append('item_id', $(this).parents().find('.open_item_actions').first().attr('id').replace('action-', ''));
                data.append("operation", "change_item_file");

                let fileType = $("#event_type input:checked").val();

                if(ext === 'pdf' && fileType === 'pdf')
                {
                    var fileReader = new FileReader();

                    fileReader.onload = function() {
                        var pdffile = new Uint8Array(this.result);

                        PDFJS.getDocument(pdffile).promise.then(function(doc) {
                            let page = [];
                            page.push(1); //Get first page

                            return Promise.all(page.map(function(num) {
                                return doc.getPage(num).then(makeThumb)
                                    .then(function(canvas) {
                                        let img = canvas.toDataURL("image/png");
                                        data.append("img", img);
                                        senddata(data);
                                    });
                            }));
                        });
                    };
                    fileReader.readAsArrayBuffer(document.getElementById("new_file").files[0]);
                }else if((ext == 'jpg' || ext == 'jpeg' || ext == 'png' || ext == 'bmp') && fileType === 'image')
                {
                    senddata(data);
                }else
                {
                    swal("<?php _t("File not accepted", "tainacan")?>", "<?php _t("Select a new file", "tainacan");?>", "error");
                }
            }
        });

        function senddata(data)
        {
            $("#change_item_file_modal").modal('hide');
            $("#modalImportMain").modal('show');
            $.ajax({
                url: path,
                type: "POST",
                data: data,
                processData: false,
                contentType: false
            }).done(function (result) {
                $("#change_item_file_modal").modal("hide");
                if(result == true)
                {
                    $("#modalImportMain").modal('hide');
                    swal(
                        {
                            title: "<?php _t("Changed", "tainacan")?>",
                            text: "<?php _t("File changed with success, page will reload", "tainacan")?>"
                        },
                        function(is_confirm)
                        {
                            location.reload();
                        }
                    );
                }else {
                    $("#modalImportMain").modal('hide');
                    swal({
                        title: "<?php _t("Error", "tainacan")?>",
                        text: "<?php _t("File could not be changed ", "tainacan")?>",
                        type: "error"
                    });
                };
            });
        }

        $('a.ac-open-file').on('click', function() {
            var item_id = $(this).parents().find('.open_item_actions').first().attr('id').replace('action-', '');
            show_modal_main();
            $.ajax({
                url: path, type: 'POST',
                data: { operation: 'press_item', object_id: item_id, collection_id: $('#collection_id').val() }
            }).done(function(r){
                var itm = $.parseJSON(r);
                if(itm) {
                    var pressPDF = new jsPDF('p','pt');
                    var baseX = 20;
                    var lMargin = baseX; // 15 left margin in mm
                    var rMargin = baseX; // 15 right margin in mm
                    var pdfInMM = 560;
                    var line_dims = { startX: 28, startY: 75, length: 540, thickness: 1 };
                    var pdfHeight = pressPDF.internal.pageSize.height;
                    var margin_bottom_page = 41.89;
                    var maxHeightOffset = pdfHeight - margin_bottom_page;

                    pressPDF.setFont("helvetica");
                    pressPDF.setFontSize(9.5);

                    var logo = $('img.tainacan-logo-cor').get(0);
                    var projectLogo = new Image();
                    projectLogo.src = $(logo).attr("src");
                    var logo_settings = { width: (projectLogo.naturalWidth * 0.48), height: (projectLogo.naturalHeight * 0.48) };

                    try {
                        pressPDF.addImage(projectLogo, 'PNG', line_dims.startX + 15, line_dims.startY - 45, logo_settings.width, logo_settings.height);
                    } catch (e) {
                        cl('Error adding tainacan\'s logo');
                    }

                    pressPDF.rect(line_dims.startX, line_dims.startY, line_dims.length, line_dims.thickness, 'F');
                    pressPDF.rect(line_dims.startX, line_dims.startY + 50, line_dims.length, line_dims.thickness, 'F');

                    pressPDF.setFontSize(8);
                    var formatted_date = "Consultado em " + getTodayFormatted();
                    pressPDF.text(formatted_date, 400, line_dims.startY - 5); // Consultado em

                    var item_date = $("#object_" + item_id + " .item-creation span").text();
                    var dist_from_top = line_dims.startY + 20;
                    pressPDF.setFontType('bold');
                    pressPDF.setFontSize(12);
                    pressPDF.text( itm.title, (line_dims.startX + 15), dist_from_top ); // Item title

                    pressPDF.setFontSize(9.5);
                    pressPDF.text( $(".item-author strong").first().text(), (line_dims.startX + 15), dist_from_top + 20); // Author
                    pressPDF.setFontType('normal');

                    var author_name = (itm.author != null) ? itm.author : 'Tainacan';
                    pressPDF.text( author_name, (line_dims.startX + 70), dist_from_top + 20);

                    var author_width = pressPDF.getTextDimensions(author_name).w + 2;
                    pressPDF.text(' em ' + item_date, (line_dims.startX + 70) + author_width, dist_from_top + 20);

                    var item_desc = itm.desc;
                    var desc_yDist = 140;
                    var desc_xDist = lMargin + baseX;
                    var desc_max_width = (pdfInMM-lMargin-rMargin);
                    if(itm.tbn) {
                        lMargin = 80;
                        pdfInMM = 490;
                        var thumb_ext = itm.tbn.ext;

                        if(thumb_ext == "jpg" || thumb_ext == "jpeg") {
                            thumb_ext = "JPEG";
                        } else {
                            thumb_ext = "PNG";
                        }
                        var item_thumb = new Image();
                        item_thumb.src = itm.tbn.url;
                        try {
                            pressPDF.addImage(item_thumb, thumb_ext, baseX*2, desc_yDist, 80, 80);
                        } catch (err) {

                        }

                        desc_xDist = lMargin + (3*baseX);
                        desc_max_width = 410;
                    }

                    var descricao = pressPDF.splitTextToSize(item_desc, desc_max_width);
                    pressPDF.text(desc_xDist, desc_yDist+10, descricao);

                    var extra_yDist = 0;
                    if(item_desc && itm.breaks && itm.breaks > 0) {
                        if(itm.tbn) {
                            extra_yDist = itm.breaks * 5;
                        } else {
                            extra_yDist = itm.breaks * 2;
                        }
                    }

                    var desc_height = Math.round(Math.round(pressPDF.getTextDimensions(descricao).h) * 1.3);
                    if(item_desc) {
                        var base_count = desc_yDist + desc_height + (baseX*2) + extra_yDist;
                    } else {
                        if(itm.tbn) {
                            var base_count = desc_yDist + 80;
                        } else {
                            var base_count = desc_yDist;
                        }
                    }

                    if(itm.attach) {
                        var base_top = 10; // var attch_marg_left = 20;
                        for (att in itm.attach) {
                            //  var attach_img = new Image(); attach_img.src = itm.attach[att].url; pressPDF.addImage(attach_img, "JPEG", 80 + attch_marg_left, 300, 80,80); attch_marg_left += 100;
                            base_top += 30;
                            pressPDF.textWithLink( itm.attach[att].title , baseX*2, base_count + base_top, { url: itm.attach[att].url, target: '_blank' });
                        }
                    }

                    if(base_top) {
                        base_count += base_top;
                    }

                    for( idx in itm.set ) {
                        if(itm.set[idx] != 'null' && itm.set[idx] !== null) {
                            if(base_count >= maxHeightOffset) {
                                pressPDF.addPage();
                                base_count = baseX;
                            }

                            var extra_line_height = 0;
                            var val_height = itm.set[idx].meta_breaks;
                            if( val_height && $.isNumeric(val_height) ) {
                                extra_line_height = 10 * val_height;
                            }

                            var extra_padding = 0;
                            var check_submeta = itm.set[idx].is_submeta;
                            if( check_submeta && (check_submeta === true) ) {
                                extra_padding = 20;
                            }
                            pressPDF.setFontStyle('bold');
                            var p = base_count + 40;
                            var meta_title = itm.set[idx].meta;

                            if(meta_title) {
                                pressPDF.text( meta_title, (baseX*2 + extra_padding) , p);
                                var f = p + 15;
                                var default_val = "--";
                                pressPDF.setFontStyle('normal');

                                if(itm.set[idx].value) {
                                    default_val = itm.set[idx].value;
                                }
                                pressPDF.text(default_val, (baseX*2 + extra_padding), f);
                                base_count = p + extra_line_height;
                            }

                            var meta_extras = itm.set[idx].extras;
                            if( meta_extras && (meta_extras.length > 0)  ) {
                                var count = 1;
                                for( ex in itm.set[idx].extras ) {
                                    var title = itm.set[idx].extras[ex].meta;
                                    var is_extra_sub_compound = itm.set[idx].extras[ex].extra_submeta;
                                    var plusX = 0;
                                    if(is_extra_sub_compound && (is_extra_sub_compound == true))
                                        plusX = 20;

                                    var extra_p = (p + 40);
                                    if(count > 1)
                                        extra_p = extra_p + (count*20);

                                    var is_extra_sub_padding = itm.set[idx].extras[ex].extra_padding;
                                    if(is_extra_sub_padding)
                                        extra_p += is_extra_sub_padding - 20;

                                    if(title) {
                                        pressPDF.setFontStyle('bold');
                                        pressPDF.text( title, (baseX*2 + 20 + plusX), extra_p);
                                        var extra_f = extra_p + 20;

                                        var vl = itm.set[idx].extras[ex].value;
                                        var extra_val = "---";
                                        if(vl)
                                            extra_val = vl;

                                        pressPDF.setFontStyle('normal');
                                        pressPDF.text(extra_val, (baseX*2 + 20 + plusX), extra_f);
                                        base_count = extra_f + extra_line_height;
                                    }
                                    count++;
                                }
                            }

                        }
                    }

                    pressPDF.save( itm.output + '.pdf');
                }

                hide_modal_main();
            });
        });

        $('a.change-owner').on('click', function(){
            var c_id = $(this).attr('data-item');
            var cur_title = $('#object_' + c_id + ' h4.item-display-title').text();
            $("#modal_change_owner_"+c_id +" .current_col").text(cur_title);
            $("#modal_change_owner_"+c_id).modal('show');
        });

    });

    function autocomplete_users(collection_id, item_id) {
        $(".autocomplete_users").autocomplete({
            source: $('#src').val() + '/controllers/user/user_controller.php?operation=list_user&collection_id=' + collection_id,
            messages: {
                noResults: '',
                results: function () { }
            },
            minLength: 2,
            select: function (event, ui) {
                var temp = $("#moderators_" + collection_id + " [value='" + ui.item.value + "']").val();
                if (typeof temp == "undefined") {
                    $("#modal_change_owner_" + item_id + " input[name='new_owner']").val(ui.item.label);
                    $("#modal_change_owner_" + item_id + " input[name='new_user_id']").val(ui.item.value);
                }
                setTimeout(function () {
                    $(".autocomplete_users").val('');
                }, 100);
            }
        });
    }

    function change_item_owner(item_id) {
        var label = $.trim($('#object_' + item_id + ' h4.item-display-title').text());
        var new_owner = $("#modal_change_owner_" + item_id + " input[name='new_user_id']").val();
        var new_owner_name =  $("#modal_change_owner_" + item_id + " input[name='new_owner']").val();
        swal({
            title: '<?php _t("Change item owner",1); ?>',
            text:  '<?php _t("Change ownership of "); ?>' + label + ' <?php _t("for"); ?> ' + new_owner_name + '?',
            type: 'warning',
            showCancelButton: true,
            closeOnConfirm: true,
            closeOnCancel: true
        }, function(isConfirm) {
            $.ajax({
                url: $('#src').val() + '/controllers/object/object_controller.php', type: 'POST',
                data: { operation: 'change_item_author', item_id: item_id, new_author: new_owner }
            }).done(function(rs){
                $("#modal_change_owner_"+item_id).modal('hide');
                location.reload();
            })
        });
    }

    function do_checkout(id){
        $.ajax({
            url: $('#src').val() + '/controllers/object/object_controller.php',
            type: 'POST',
            data: {operation: 'check-out', collection_id: $('#collection_id').val(), object_id: id}
        }).done(function (result) {
            wpquery_filter();
            showAlertGeneral('<?php _e('Success!','tainacan') ?>','<?php _t('Checkout enabled!') ?>','success');
        });
    }

    function discard_checkout(id){
        $.ajax({
            url: $('#src').val() + '/controllers/object/object_controller.php',
            type: 'POST',
            data: {operation: 'check-out', collection_id: $('#collection_id').val(), object_id: id,value:''}
        }).done(function (result) {
            showAlertGeneral('<?php _e('Success!','tainacan') ?>','<?php _t('Checkout disabled!') ?>','success');
            wpquery_filter();
        });
    }

    function do_checkin(id){
        $('.dropdown-menu .dropdown-hover-show').trigger('mouseout');
        swal({
                title: "<?php _t('Checkin') ?>",
                text: "<?php _t('Checkin motive:') ?>",
                type: "input",
                showCancelButton: true,
                closeOnConfirm: true,
                inputPlaceholder: "<?php _t('Type check in motive') ?>"
            },
            function(inputValue){
                if (inputValue === false) return false;

                if (inputValue === "") {
                    swal.showInputError("<?php _t('You need to write something!') ?>");
                    return false
                }
                show_modal_main();
                $.ajax({
                    url: $('#src').val() + '/controllers/object/object_controller.php',
                    type: 'POST',
                    data: {operation: 'check-in', collection_id: $('#collection_id').val(), object_id: id,motive:inputValue}
                }).done(function (result) {
                    wpquery_filter();
                    hide_modal_main();
                    showAlertGeneral('<?php _t('Success!') ?>','<?php _t('Checkin done!') ?>','success');
                    $("#form").html('');
                    $('#main_part').hide();
                    $('#display_view_main_page').hide();
                    $('#loader_collections').hide();
                    $('#configuration').html(result).show();
                    $('.dropdown-toggle').dropdown();
                    $('.nav-tabs').tab();
                });
            });
    }
</script>
