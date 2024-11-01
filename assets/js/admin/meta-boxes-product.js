jQuery(function ($) {
	function disable_form() {
		$('.vkcommerce-action-btn').prop('disabled', true)
		$('.vkcommerce-action-lnk').addClass('disabled')
	}

	function enable_form() {
		$('.vkcommerce-action-btn').prop('disabled', false)
		$('.vkcommerce-action-lnk').removeClass('disabled')
	}

	function publish_product(productId) {
		var $spinner = $('#vkcommerce-publish-box-spinner');

		$spinner.addClass('is-active');

		disable_form();

		$.post(ajaxurl, {
			'action': 'vkcommerce_publish_product',
			'product_id': productId
		})
			.done(function (response) {
				if (response.message) {
					alert(response.message);
				}

				$publish_box.html(response.html);
			})
			.fail(function () {
				alert("Server error");
			})
			.always(function () {
				$spinner.removeClass('is-active');
				enable_form();
			});
	}

    function unpublish_product(productId) {
        var $spinner = $('#vkcommerce-publish-box-spinner');

        $spinner.addClass('is-active');

        disable_form();

        $.post(ajaxurl, {
            'action': 'vkcommerce_delete_product',
            'product_id': productId
        })
            .done(function (response) {
                if (response.message) {
                    alert(response.message);
                }

                $publish_box.html(response.html);
            })
            .fail(function () {
                alert("Server error");
            })
            .always(function () {
                $spinner.removeClass('is-active');
                enable_form();
            });
	}

	var $publish_box = $('#vkcommerce-publish-meta-box .inside');

	$(document).on('click', '#vkcommerce-publish', function () {
		var $button = $(this);
		var productId = $button.data('productId');
		var productStatus = $button.data('productStatus');
		var message = wp.i18n.__('Do you really want to publish a product?', 'vkcommerce');

		switch (productStatus) {
			case 'synced':
			case 'outdated':
				publish_product(productId);

				return false;

			case 'queued_to_publish':
				message = wp.i18n.__('A product is queued to publish. Do you really want to publish the product now?', 'vkcommerce');

				break;

			case 'queued_to_unpublish':
				message = wp.i18n.__('A product is queued to unpublish. Do you really want to publish the product now?', 'vkcommerce');

				break;
		}

		if (confirm(message)) {
			publish_product(productId);
		}

		return false;
	});

	$(document).on('click', '#vkcommerce-delete', function () {
		var $link = $(this);

		if ($link.hasClass('disabled')) {
			return false;
		}

        var productId = $link.data('productId');
        var productStatus = $link.data('productStatus');
        var message = wp.i18n.__('Do you really want to unpublish a product?', 'vkcommerce');

        switch (productStatus) {
            case 'queued_to_publish':
                message = wp.i18n.__('A product is queued to publish. Do you really want to unpublish the product now?', 'vkcommerce');

                break;

            case 'queued_to_unpublish':
                message = wp.i18n.__('A product is queued to unpublish. Do you really want to unpublish the product now?', 'vkcommerce');

                break;
        }

        if (confirm(message)) {
            unpublish_product(productId);
        }

        return false;
	});

	$('.vkcommerce-use-general-settings').click(function () {
		var $this = $(this);
		var $target_field = $('#' + $this.data('for'));

		$target_field.prop('disabled', $this.is(':checked'));
		$target_field.val($target_field.data('generalValue'));
	});
});

