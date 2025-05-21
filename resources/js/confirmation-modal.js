export default class ConfirmationModal
{
    constructor()
    {
        let modal = document.createElement('div');
        modal.className = 'modal fade';
        modal.setAttribute('tabindex', '-1');
        modal.setAttribute('id', 'confirmation-modal');

        let dialog = document.createElement('div');
        dialog.className = 'modal-dialog';

        let content = document.createElement('div');
        content.className = 'modal-content';

        // header
        let header = document.createElement('div');
        header.className = 'modal-header';

        let title = document.createElement('h5');
        title.className = 'modal-title';

        header.append(title);

        let close = document.createElement('button');
        close.className = 'btn-close';
        close.type = 'button';
        close.setAttribute('data-bs-dismiss', 'modal');

        header.append(close);

        content.append(header);

        // body
        let body = document.createElement('div');
        body.className = 'modal-body';

        content.append(body);

        // footer
        let footer = document.createElement('div');
        footer.className = 'modal-footer';

        let no = document.createElement('button');
        no.className = 'btn btn-secondary';
        no.type = 'button';
        no.setAttribute('data-bs-dismiss', 'modal');
        no.textContent = 'No';

        footer.append(no);

        let yes = document.createElement('button');
        yes.className = 'btn btn-primary';
        yes.type = 'button';
        yes.setAttribute('id', 'confirm-yes');
        yes.textContent = 'Yes, I am sure';

        footer.append(yes);

        content.append(footer);

        dialog.append(content);

        modal.append(dialog);

        $('body').append(modal);

        // click on .confirm-link
        $('.main-content').on('click', 'a.confirm-link', (e) => {
            this.clickConfirmLink(e);
        });
    }

    clickConfirmLink(event)
    {
        event.stopPropagation();
        event.preventDefault();

        let $link = $(event.currentTarget);
        let href  = $link.attr('href');

        let title   = 'Are you sure?';
        let message = 'Are you sure you want to perferm this task?'; 
        let btn     = 'primary';

        if ($link.attr('data-title')) {
            title = $link.attr('data-title');
        }

        if ($link.attr('data-confirm-message')) {
            message = $link.attr('data-confirm-message');
        }

        if ($link.attr('data-btn')) {
            btn = $link.attr('data-btn');
        }

        $('#confirmation-modal .modal-title').empty().append(title);
        $('#confirmation-modal .modal-body').empty().append('<p>' + message + '</p>');
        $('#confirmation-modal #confirm-yes').removeClass().addClass('btn btn-' + btn);
        $('#confirmation-modal .modal-footer').show();

        $('#confirmation-modal').modal('show');

        $('#confirmation-modal').on('click', '#confirm-yes', function() {
            $.post(href, function(data) {
                window.location.reload();
            }).fail(function(data) {
                $('#confirmation-modal .modal-title').empty().append('Error:');
                $('#confirmation-modal .modal-body').empty().append('<p class="alert alert-danger">' + data.responseJSON.message + '</p>');
                $('#confirmation-modal .modal-footer').hide();

                $('#confirmation-modal').modal('show');
            });
        });
    }
}

