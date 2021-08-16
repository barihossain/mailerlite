@extends('layout')

@section('page-title')
    All subcribers
@endsection

@section('css-block')
    <link href="https://cdn.datatables.net/1.10.25/css/dataTables.bootstrap5.min.css" rel="stylesheet">
@endsection

@section('js-block')
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-validate/1.19.0/jquery.validate.js"></script>
    <script src="https://cdn.datatables.net/1.10.25/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.10.25/js/dataTables.bootstrap5.min.js"></script>
    <script type="text/javascript">
        function createAlert(messages, alertClass) {
            return  '<div class="alert '+ alertClass +' alert-dismissible fade show" role="alert">' +
                    messages +
                    '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>' +
                    '</div>';
        }

        function processErrorMessages (messages) {
            var errorMessages = [];
            $.each( messages, function( key, value ) {
                errorMessages.push(value);
            });

            return errorMessages.join('<br>');
        }

        function printAlerts(message, alertClass, container) {
            var alert = createAlert(message, alertClass);
            $('#' + container).html(alert);
        }
        
        $(function () {
            var table = $('#subscribers-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: "{{ route('subscribers.list') }}",
                columns: [
                    {data: 'email'},
                    {
                        data: 'name', 
                        render: function(data, type, row) {
                            var dataForForm = {'name': row.name, 'email' : row.email, 'country': row.country};
                            return '<a class="edit-subscriber" href="#" data-subscriber="' + escape(JSON.stringify(dataForForm)) + '">' + data + '</a>';
                        }
                    },
                    {
                        data: 'country'
                    },
                    {data: 'subscribe_date'},
                    {data: 'subscribe_time'},
                    {
                        render: function(data, type, row) {
                            return '<a class="btn btn-outline-danger btn-sm delete-subscriber" href="#" data-email="' + row.email + '">Delete</a>';
                        }
                    }
                ],
                ordering: false,
            });

            $( '#subscribers-table' ).on('click', '.edit-subscriber', function(e) {
                e.preventDefault();
                let subscriber = JSON.parse(unescape($(this).data('subscriber')));
                $('#subscriber-form').trigger('reset');
                $('#subscriber-modal-label').html('Edit &ldquo;' + subscriber.email + '&rdquo;');
                $('#name').val(subscriber.name);
                $('#country').val(subscriber.country);
                $('#email').val(subscriber.email);
                $('#email-field').hide();
                $('#save-button').val('update');
                $('#modal-alert-area').empty();
                $('#subscriber-modal').modal('show');
            });

            $('#subscribers-table' ).on('click', '.delete-subscriber', function(e) {
                e.preventDefault();
                let subscriberEmail = $(this).data('email');
                console.log(subscriberEmail);
                
                $.ajax({
                    url: '/subscribers/' + subscriberEmail,
                    type: 'DELETE',
                    success: function(result) {
                        if(result.success) {
                            table.ajax.reload(null, false);
                            printAlerts(subscriberEmail + ' successfully deleted', 'alert-success', 'list-alert-area');
                        }
                    },
                    error: function (xhr) {
                        console.log(xhr.responseText);
                        var responseJson = JSON.parse(xhr.responseText);
                        var errors = processErrorMessages(responseJson.errors);
                        printAlerts(errors, 'alert-danger', 'list-alert-area');
                    }
                });
            });

            $('#show-add-new-modal').click(function() {
                $('#subscriber-form').trigger("reset");
                $('#subscriber-modal-label').html("New subsriber details");
                $('#email-field').show();
                $('#save-button').val('create');
                $('#modal-alert-area').empty();
                $('#subscriber-modal').modal('show');
            });
            
            $('#save-button').click(function() {
                var operation = $(this).val();
                var email = $('#email').val();
                var name = $('#name').val();
                var country = $('#country').val();
                var type = 'POST';
                var url = '/subscribers'
                var data = {
                    name,
                    email,
                    country
                };
                var successMessage = 'New subscriber successfully created';
                if(operation == 'update') {
                    type = 'PUT';
                    url += '/' + email;
                    delete data.email;
                    successMessage = 'Subscriber details successfully updated';
                }

                $.ajax({
                    url: url,
                    type: type,
                    data: data,
                    success: function(result) {
                        if(result.success) {
                            printAlerts(successMessage, 'alert-success', 'modal-alert-area');
                            table.ajax.reload(null, false);

                            if(operation==='create') {
                                $('#subscriber-form').trigger('reset');
                                $('#save-button').val('create');
                            }
                        }
                    },
                    error: function (xhr) {
                        var responseJson = JSON.parse(xhr.responseText);
                        var errors = processErrorMessages(responseJson.errors);
                        printAlerts(errors, 'alert-danger', 'modal-alert-area');
                    }
                });
            });
        });
    </script>
@endsection

@section('extra-buttons')
    <button class="btn btn-outline-primary ms-auto" id="show-add-new-modal">Add new</button>
@endsection

@section('body')
    <div id="list-alert-area"></div>
    <table class="table table-hover" id="subscribers-table">
        <thead>
            <tr>
                <th>Email</th>
                <th>Name</th>
                <th>Country</th>
                <th>Subscribe date</th>
                <th>Subscribe time</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
        </tbody>
    </table>
    <div class="modal fade" id="subscriber-modal" tabindex="-1" aria-labelledby="subscriber-modal-label" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="subscriber-modal-label">New subsriber details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div id="modal-alert-area"></div>
                    <form id="subscriber-form">
                        <div class="mb-3">
                            <label for="name" class="col-form-label">Name:</label>
                            <input type="text" class="form-control" id="name">
                        </div>
                        <div class="mb-3" id="email-field">
                            <label for="email" class="col-form-label">Email:</label>
                            <input type="text" class="form-control" id="email">
                        </div>
                        <div class="mb-3">
                            <label for="country" class="col-form-label">Country:</label>
                            <select id="country" class="form-select mb-1">
                                <option value="">Select Country</option>
                                @foreach ($countries as $country)
                                    <option value="{{ $country }}">{{ $country }}</option>
                                @endforeach
                            </select>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">                   
                    <button type="button" class="btn btn-primary" id="save-button" value="create">Save</button>
                </div>
            </div>
        </div>
    </div>
@endsection