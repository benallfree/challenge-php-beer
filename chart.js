function init() {
    var max_value = 0;
    var values_count = 0;
    var chart_height = 200;
    var bar_width = 50;
    var bar_spacing = 10;
    var bar_gap = 5;
    
    jQuery('#chart .value').each(function (index, value) {
        var new_value = parseInt(jQuery(this).attr('value'));
        if (new_value > max_value) {
            max_value = new_value;
        }
        values_count ++;
    });
    
    available_width = $('#chart').width() - ( (values_count*bar_spacing) + bar_gap);
    bar_width = Math.floor(available_width / values_count);
    
    var i = 0;
    
    jQuery('#chart .value').each(function (index, value) {
        var new_value = parseInt(jQuery(this).attr('value'));
        var timestamp = jQuery(this).attr('timestamp');
        
        jQuery(this).css({height: ((new_value / max_value) * chart_height) + 'px', width: bar_width + 'px',}).css('left', i * (bar_width + bar_spacing) + bar_gap + 'px').get(0).innerHTML = new_value;
        i++;
    });
}