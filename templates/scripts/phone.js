(function($) {
	$.fn.phone = function(data) {
		this.each(function(){			var container = $(this);			if (!container.hasClass('compiled')) {
				var el = $('select',this).hide().after($('<div class="sel"></div><ul></ul>')),
					ph = $('input',this),
					sel = el.next('.sel'),
					ul = sel.next('ul'),
					stid = el.val(),
					code = '+'+data[stid][0];
				$('option',el).each(function(i){
					var o = $(this);
						v = this.value;
					$('<li></li>').text(o.text()).data({
						code: data[v][0],
						pos: data[v][1],
						value: v
					}).prepend($('<b class="flag"></b>').css({backgroundPosition:'0 -'+data[v][1]+'px'})).appendTo(ul);
				});
				if (ph.val()=='' || ph.val()==ph.attr('placeholder')) ph.val(code).data({code:code});
				$('<b class="flag"></b>').css({backgroundPosition:'0 -'+data[stid][1]+'px'}).appendTo(sel);
				sel.click(function(){
					ul.toggle().parent().toggleClass('active');
					return false;
				});
				$(document).click(function(){
					ul.hide().parent().removeClass('active');
				});
				$('li',ul).click(function(){
					var li = $(this),
						id = li.data('value'),
						c = '+'+li.data('code'),
						p = li.data('pos'),
						phone = ph.val();
					li.addClass('selected').siblings().removeClass('selected');
					sel.children('b.flag').css({backgroundPosition:'0 -'+p+'px'});
					if (phone.indexOf(ph.data('code'))==0) phone = phone.replace(ph.data('code'),c);
					else phone = c;
					ph.val(phone).data({code:c});
					el.val(id);
				}).first().addClass('selected');
				ph.keyup(function(){
					if (this.value.indexOf(ph.data('code'))!=0) this.value = ph.data('code');
				});
				container.addClass('compiled');
			}
		});
	}
})(jQuery);