var Resource = function(){
    var body = $('body'),
        form = $('.resource_form'),
        input = form.find('.search_input'),
        search_results = $('.search_results .wrapper'),
        search_button = form.find('.search_button'),
        search_timer = null;

    body.on('click', '.load_more_resources', function(e){
        e.preventDefault();
        var load = $('.load_more_resources'),
            container = $('.load_container');

        load.text('loading...');
        $.ajax({
            'method': 'get',
            'url': load.attr('href'),
            'dataType': 'json',
            'success': function(resp){
                search_results.append(resp['results']);
                load.remove();
                container.remove();
                body.animate({
                    'scrollTop': $('#' + resp['id']).offset().top
                });
            }
        });
    });

    function doSearch(){
        $.ajax({
            'method': 'get',
            'url': form.attr('action'),
            'data': form.serialize(),
            'dataType': 'json',
            'success': function(resp){
                search_results.empty().append(resp['results']);
                search_button.text('search');
            }
        });
    }

    body.on('click', '.search_item', function(e){
        e.preventDefault();
        var item = $(this),
            val = item.attr('data-search');

        input.val(input.val() + ' ' + val);
        search_button.text('searching...');

        clearTimeout(search_timer);
        search_timer = null;
        search_timer = setTimeout(function(){
            doSearch();
        }, 1000);
    });
};