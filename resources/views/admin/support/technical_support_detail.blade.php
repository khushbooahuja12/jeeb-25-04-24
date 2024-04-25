@extends('admin.layouts.dashboard_layout')
@section('content')
    <link rel="stylesheet" href="{{ asset('assets/css/customer_support.css') }}">
    <div class="container-fluid">
        <div class="page-title-box">
            <div class="row align-items-center">
                <div class="col-sm-6">
                    <h4 class="page-title">Technical Support Detail</h4>
                </div>
                <div class="col-sm-6">
                    <ol class="breadcrumb float-right">
                        <li class="breadcrumb-item"><a href="<?= url('admin/dashboard') ?>">Home</a></li>
                        <li class="breadcrumb-item"><a href="<?= url('admin/technical-support') ?>">Technical Support</a>
                        </li>
                        <li class="breadcrumb-item active">Detail</li>
                    </ol>
                </div>
            </div>
        </div>
        @include('partials.errors')
        @include('partials.success')
        <div class="row">
            <div class="col-12">
                <div class="card m-b-30">
                    <div class="card-body">
                        <table class="table table-bordered dt-responsive nowrap">
                            <thead>
                                <tr>
                                    <th>User</th>
                                    <td>{{ $support->getUser ? $support->getUser->name : '+' . $support->getUser->country_code . '-' . $support->getUser->mobile }}
                                    </td>
                                </tr>
                                <tr>
                                    <th>Subject</th>
                                    <td>{{ $support->subject }}</td>
                                </tr>
                                <tr>
                                    <th>Email</th>
                                    <td>{{ $support->email }}</td>
                                </tr>
                                <tr>
                                    <th>Description</th>
                                    <td>{{ $support->description }}</td>
                                </tr>
                                <tr>
                                    <th>Screenshots</th>
                                    <td>
                                        <?php
                                    if ($support->screenshots != ''):
                                        $imageIds = explode(',', $support->screenshots);
                                        foreach ($imageIds as $key => $value):
                                            $file = App\Model\File::find($value);
                                            ?>
                                        <img src="{{ !empty($file) ? asset('images/support_images') . '/' . $file->file_name : asset('assets/images/no_img.png') }}"
                                            width="150" height="100">
                                        <?php
                                        endforeach;
                                    endif;
                                    ?>
                                    </td>
                                </tr>
                                <tr>
                                    <th>Open/Close ticket</th>
                                    <td>
                                        <div class="mytoggle">
                                            <label class="switch">
                                                <input class="switch-input set_status<?= $support->id ?>" type="checkbox"
                                                    value="{{ $support->status }}"
                                                    <?= $support->status == 1 ? 'checked' : '' ?> id="<?= $support->id ?>"
                                                    onchange="checkStatus(this)">
                                                <span class="slider round"></span>
                                            </label>
                                        </div>
                                    </td>
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <div class="mesgs">
            @if ($messages->count())
                <div class="msg_history" id="msg_history">
                    @foreach ($messages as $key => $value)
                        <div class="<?= $value->from == 'user' ? 'incoming_msg' : 'outgoing_msg' ?>">
                            <div class="<?= $value->from == 'user' ? 'received_msg' : 'sent_msg' ?>">
                                <div class="<?= $value->from == 'user' ? 'received_withd_msg' : '' ?>">
                                    <p>
                                        <?php if ($value->type == 'image'): ?>
                                        <img src="<?= $value->getFile && $value->getFile->file_path ? asset('/') . $value->getFile->file_path . $value->getFile->file_name : asset('assets/images/dummy_user.png') ?>"
                                            width="100" height="75" />
                                        <?php else: ?>
                                        {{ $value->message }}
                                        <?php endif; ?>
                                    </p>
                                    <span
                                        created_at="<?= strtotime($value->created_at) ?> class="changeUtcDateTime">{{ date('H:i A | d-m-Y', strtotime($value->created_at)) }}</span>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="msg_history">
                    <center>No messages found !</center>
                </div>
            @endif
            <form method="post" id="chat-form" autocomplete="off">
                <label id="textmessage-error" class="error" for="textmessage"></label>
                <div class="type_msg">
                    <div class="input_msg_write">
                        <textarea type="text" cols="85" rows="3" name="message" autocomplete="off" id="textmessage"
                            class="write_msg" placeholder="Type a message" <?= $support->status == 0 ? 'disabled' : '' ?> style="cursor:<?= $support->status == 0 ? 'not-allowed' : '' ?>"></textarea>
                        <button class="msg_send_btn" id="selectfile" type="button"
                            style="background: none;cursor: <?= $support->status == 0 ? 'not-allowed' : 'pointer' ?>;"
                            <?= $support->status == 0 ? 'disabled' : '' ?>>
                            <img src="<?= asset('/assets/images/attachment.png') ?>" />
                        </button>
                        <input id="photo_input" type="file" accept="image/*" style="display:none" />
                    </div>
                </div>
            </form>
        </div>
    </div>
    <script>
        $(document).ready(function() {
            var elmnt = document.getElementById("msg_history");
            var y = elmnt.scrollHeight;
            $("#msg_history").scrollTop(y);
        });

        $("#chat-form").submit(function(e) {
            e.preventDefault();
            var chat_content = $(this).find('[name=message]').val();
            if (chat_content != '') {
                if (!chat_content.replace(/\s/g, '').length) {
                    $("#textmessage-error").text('Please enter some text');
                } else {
                    $.ajax({
                        url: "<?= url('admin/support/send-message') ?>",
                        type: 'post',
                        data: {
                            chat_content: chat_content,
                            user_id: '<?= $support->fk_user_id ?>',
                            support_id: '<?= $support->id ?>'
                        },
                        dataType: 'json',
                        cache: false
                    }).done(function(response) {
                        if (response.error_code == 200) {
                            var str = '<div class="outgoing_msg">' +
                                '<div class="sent_msg">' +
                                '<div class="">' +
                                '<p>' + chat_content + '' +
                                '</p>' +
                                '<span>' + response.result.created_at + '</span>' +
                                '</div>' +
                                '</div>' +
                                '</div>';
                            $(".msg_history").append(str);

                            var elmnt = document.getElementById("msg_history");
                            var y = elmnt.scrollHeight;
                            $("#msg_history").scrollTop(y);
                        } else {
                            $("#textmessage-error").text('Some error found');
                        }
                    });
                    $(this).find('[name=content]').val('');
                }

            } else {
                $("#textmessage-error").text('Please enter some text');
            }
        });
        $("#textmessage").keypress(function(e) {
            if (this.value != '') {
                $("#textmessage-error").text('');
            }
            if (e.keyCode == 13 && e.shiftKey) {
                var content = this.value;
                var caret = getCaret(this);
                this.value = content.substring(0, caret) + content.substring(caret, content.length);
                e.stopPropagation();
            } else if (e.which == 13) {
                $("#chat-form").submit();
                $(this).val("");
                e.preventDefault();
            }
        });

        function getCaret(el) {
            if (el.selectionStart) {
                return el.selectionStart;
            } else if (document.selection) {
                el.focus();
                var r = document.selection.createRange();
                if (r == null) {
                    return 0;
                }
                var re = el.createTextRange(),
                    rc = re.duplicate();
                re.moveToBookmark(r.getBookmark());
                rc.setEndPoint('EndToStart', re);
                return rc.text.length;
            }
            return 0;
        }

        $("#selectfile").on('click', function() {
            $("#photo_input").click();
        })

        $('#photo_input').on('change', function() {
            var fd = new FormData();
            var files = $('#photo_input')[0].files;

            if (files.length > 0) {
                fd.append('file', files[0]);
                fd.append('user_id', <?= $support->fk_user_id ?>);
                fd.append('support_id', <?= $support->id ?>);

                $.ajax({
                    url: '<?= url('admin/support/send-image') ?>',
                    type: 'post',
                    data: fd,
                    contentType: false,
                    processData: false,
                    success: function(response) {
                        if (response.error_code == 200) {
                            var str = '<div class="outgoing_msg">' +
                                '<div class="sent_msg">' +
                                '<div class="">' +
                                '<p><img src="' + response.result.image + '" width="100" height="75">' +
                                '</p>' +
                                '<span>' + response.result.created_at + '</span>' +
                                '</div>' +
                                '</div>' +
                                '</div>';
                            $(".msg_history").append(str);

                            var elmnt = document.getElementById("msg_history");
                            var y = elmnt.scrollHeight;
                            $("#msg_history").scrollTop(y);
                        } else {
                            $("#textmessage-error").text('Some error found');
                        }
                    },
                });
            } else {
                $("#textmessage-error").text('Please select an image');
            }
        });

        function checkStatus(obj) {
            var id = $(obj).attr("id");

            var checked = $(obj).is(':checked');
            if (checked == true) {
                var status = 1;
                var statustext = 'open';
            } else {
                var status = 0;
                var statustext = 'close';
            }

            if (confirm("Are you sure, you want to " + statustext + " this ticket ?")) {
                $.ajax({
                    url: '<?= url('admin/change_status') ?>',
                    type: 'post',
                    dataType: 'json',
                    data: {
                        method: 'changeTicketStatus',
                        status: status,
                        id: id
                    },
                    cache: false,
                }).done(function(response) {
                    if (response.status_code == 200) {
                        // var success_str = '<div class="alert alert-success fade show alert-dismissible" style="margin-top: 18px;z-index: 99;">'
                        //         + '<a href="#" class="close" data-dismiss="alert" aria-label="close" title="close">×</a>'
                        //         + '<strong>Status updated successfully</strong>.'
                        //         + '</div>';
                        // $(".ajax_alert").html(success_str);

                        // $('.user' + id).find("p").text(response.result.status);

                        window.location.reload();
                    } else {
                        var error_str =
                            '<div class="alert alert-danger fade show alert-dismissible" style="margin-top: 18px;z-index: 99;">' +
                            '<a href="#" class="close" data-dismiss="alert" aria-label="close" title="close">×</a>' +
                            '<strong>Some error found</strong>.' +
                            '</div>';
                        $(".ajax_alert").html(error_str);
                    }
                });
            } else {
                if (status == 1) {
                    $(".set_status" + id).prop('checked', false);
                } else if (status == 0) {
                    $(".set_status" + id).prop('checked', true);
                }
            }
        }
    </script>
@endsection
