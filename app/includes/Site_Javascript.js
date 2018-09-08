/* 
    small function to loop through all the data attributes of an element and 
    return then in a simple object
*/

getData = function(elm){
    var data = {};
    $.each(elm.attributes, function(){
        if(this.name.indexOf('data') >= 0) {
            data[this.name.split('-')[1]] = this.value;
        }
    });
    return formInputs;
};

//Simulate javascript alert dialog (using bootstrap modal)
function jAlert(message,title){
    var title = title || 'Warning';

    html  = '<div class="modal-dialog">';
    html += '<div class="modal-content">';
    html += '<div class="modal-header">';
    html += '<h5 class="modal-title">' + title + '</h5>'
    html += '<a class="close" data-dismiss="modal">×</a>';
    html += '</div>';  //modalHeader
    html += '<div class="modal-body">';
    html += message;
    html += '</div>'; //modal Body
    html += '<div class="modal-footer">';
    html += '<span class="btn btn-default">OK</span>';    
    html += '</div>';  // footer
    html += '</div>';  // content
    html += '</div>';  // dialog

    $("<div></div>")
        .html(html)
        .attr('id','modalConfirmDiv')
        .attr('tabindex','-1')
        .addClass('modal fade')
        .appendTo('body');
    
    $("#modalConfirmDiv .btn").click(function(){
        $("#modalConfirmDiv").modal('hide');
    });

    $("#modalConfirmDiv").modal(
        {show:true}
    );
    
    $("#modalConfirmDiv").on('hidden.bs.modal', function () {
      $(this).data('bs.modal', null).remove();
    });
}



//simulate javascript confirm function (using bootstrap modal)
function jConfirm(message, callbackOK, callbackCancel, btnTextYes, btnTextNo){
    var callbackCancel = callbackCancel || function(){},
        btnTextYes = btnTextYes || "Ok",
        btnTextNo = btnTextNo || "Cancel";
    
    //$("#modalConfirmDiv").data('bs.modal,null);
    

    html  = '<div class="modal-dialog">';
    html += '<div class="modal-content">';
    html += '<div class="modal-header">';
    html += '<h5 class="modal-title">Confirmation</h5>'
    html += '<a class="close" data-dismiss="modal">×</a>';
    html += '</div>';  //modalHeader
    html += '<div class="modal-body">';
    html += message;
    html += '</div>'; //modal Body
    html += '<div class="modal-footer">';
    
    if (btnTextYes!='') {
        html += '<span class="btn btn-success">' + btnTextYes + '</span>';
    }
    if (btnTextNo!='') {
        html += '<span class="btn btn-danger">' + btnTextNo + '</span>';
    }
    
    html += '</div>';  // footer
    html += '</div>';  // content
    html += '</div>';  // dialog

    
    $("<div></div>")
        .html(html)
        .attr('id','modalConfirmDiv')
        .attr('tabindex','-1')
        .addClass('modal fade')
        .appendTo('body');
    
    $("#modalConfirmDiv .btn-success").click(function(){
        callbackOK();
    });
    $("#modalConfirmDiv .btn-danger").click(function(){
        callbackCancel();
        $("#modalConfirmDiv").modal('hide');
    });
    $("#modalConfirmDiv").modal(
        {
            show:true
        }
    );
    
    $("#modalConfirmDiv").on('hidden.bs.modal', function () {
      $(this).data('bs.modal', null).remove();
    });
}


/*
    A jquery replacement for the ColdFusion.navigate function...
    
    Overloading options:
        (url,container,callback)
        (url,container,callback,errorhandler)
        (url,container,callback,errorhandler,form)
        (url,container,callback,form)
        (url,container,form)

        url:            string      (page to be called)
        container:      string      (ID of container where content gets displayed)
        callback:       function    (function to call when ajax is completed)
        errorhander:    function    (function to call when ajax fails - defaults to callback)
        form:           string      (ID of form to be submitted)
                        object      (key value pairs of data to be submitted)
*/
    
var navigate = function(url,container,parm1,parm2,parm3){
    var callback = function(){},
        errorhandler = function(){};
    
    
    if( typeof parm1 === 'function'){
        callback = parm1;
        errorhandler = parm1;
        if (typeof parm2 === 'function'){
            errorhandler = parm2;
            if (typeof parm3 !== 'undefined'){
                var form = parm3;
            }
        } else {
            var form = parm2;
        }
    } else {
        var form = parm1;
    }
    
    
    //form can be json key value pairs, or the id of a form, or blank
    if (typeof form === 'object') {
        data = form;
    } else if(form) {
        data = $('#' + form).serialize();
    } else {
        data = '';
    }
    
    $.ajax(
        {
            type: 'POST',
            url:  url,
            data: data,
            dataType:'html'
        }
    ).fail(function(xhr, status, error) {
        $("#" + container).html(xhr.responseText);
        errorhandler();
    }).done(function( data, textStatus, xhr ) {
        //populate the container and execute any script tags in response
        $("#" + container).html(xhr.responseText);
        callback();
    });
};



/*
    function to dynamically create a form (with javascript) and submit it.
    
    Requires jQuery
    
    Execute like...
    
    post('ACVwb100.cfm', {DeleteID: visitID});
    post('ACVwb100.cfm', {DeleteID: visitID}, 'get');
    
    (careful not ot have a data input called "submit" or it will break)
*/

function post(path, params, method) {
    method = method || "post"; // Set method to post by default if not specified.

    // The rest of this code assumes you are not using a library.
    // It can be made less wordy if you use one.
    var form = document.createElement("form");
    form.setAttribute("method", method);
    form.setAttribute("action", path);

    for(var key in params) {
        if(params.hasOwnProperty(key)) {
            var hiddenField = document.createElement("input");
            hiddenField.setAttribute("type", "hidden");
            hiddenField.setAttribute("name", key);
            hiddenField.setAttribute("value", params[key]);

            form.appendChild(hiddenField);
         }
    }

    document.body.appendChild(form);
    form.submit();
}

/*
    create a jquery function similar to $.getJSON but have it use POST instead of GET
*/

postJSON = function(url, data, callback){
   
    $.ajax({
        url: url,
        type: "POST",
        dataType: "json",
        data:data
    })
    .done(function(data){
        callback(data);
    })
    .fail(function(a, b){
        console.log('failed:', b);
        $("body").append(a.responseText);
    });
}
