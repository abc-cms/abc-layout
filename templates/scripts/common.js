$(document).ready(function(){
	//валидация форм
	if ($.isFunction($.fn.validate)) {
		$('form.validate').each(function(){
			$(this).validate();
		});
	}

	//очитска урл от путстых значений
	$('form.form_clear').submit(function(){
		$(this).find('select,input').each(function(){
			if($(this).val()=='' || $(this).val()=='0-0') $(this).removeAttr('name');
		});
	});

	//мультичексбокс
	$(document).on("change",'.form_multi_checkbox .data input',function(){
		var arr = [];
		var i = 0;
		$(this).parents('.data').find('input:checked').each(function(){
			arr[i] = $(this).val();
			i++;
		});
		$(this).parents('.data').next('input').val(arr);
	});
	//min-max
	$(document).on("change",'.form_input2 input',function(){
		var min = parseInt($(this).parents('.form_input2').find('input.form_input2_1').val());
		var max = parseInt($(this).parents('.form_input2').find('input.form_input2_2').val());
		$(this).parents('.form_input2').find('input[type=hidden]').val(min+'-'+max);
	});

	//добавление товара в корзину
	$('.js_buy').click(function(){
		var basket	= $('#basket_info'),
			product	= $(this).data('id'),
			price	= $(this).data('price'),
			count	= 1,
			counter = $('.count',basket),
			total = $('.total',basket),
			basket_count = parseInt(counter.text()),
			basket_total = parseInt(total.text());
		$.getJSON('/ajax.php',{
				file:		'basket',
				action:		'add_product',
				product:	product,
				count:		count
			},function (data) {
				if (data.done){
					counter.text(data.count);
					total.text(data.total);
				} else alert(data.message);
			}
		);
		//моментальное изменение количества и цены товароы на старнице
		basket_count+= count;
		basket_total+= price * count;
		//количество знаков после запятой
		basket_total = basket_total.toFixed();
		counter.text(basket_count);
		total.text(basket_total);
		$('.full',basket).show();
		$('.empty',basket).hide();
		$('#basket_message').modal();
		return false;
	});
});