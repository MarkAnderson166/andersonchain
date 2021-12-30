
$(function() {
  $('#transactionform').submit(function(e) {
    e.preventDefault();
    sendtransactionRequest();
  });
});


// send POST request with all form data
function sendtransactionRequest() {
  remove_msg();
  
  $.ajax({
    url: newTransactionUrl,
    method: 'POST',
    data: $('#transactionform').serialize(),
    dataType: 'json',
    success: function(data) {
      $('#server_response').addClass('success');
      $('#server_response span').text('New transaction added to mempool');
      populateMempool();
    },
    error: function(jqXHR) {
      try {
        var $e = JSON.parse(jqXHR.responseText);
        $('#server_response').addClass('error');
        $('#server_response span').text('Error from server: ' +$e.error);
      }
      catch (error) {
        console.log('Could not parse JSON error message: ' +error);
      }
    }
  });
}



function populateMempool() {
  $('#memTable').empty();
  $('#memTableHeader').empty();
  $('#memTableHeader').append('Current Mempool: 0');
  var mempoolcounter = 0;

  $.getJSON(transactionDBurl, function (data, xhr) {
    $.each(data.reverse(), function (key, entry) {
    //$.each(data, function (key, entry) {
      mempoolcounter++;
        // I need the arg being passed to the func to be dynamically generated
        // so this is a section of html, with an in-line javascript func call,
        // being inserted into the html from the .js file.... yuck
      let htmlInsert = '<p id="'+entry['Hash']+'">'+
                      entry['Hash'].slice(0,24)+'.... Value: '+entry['Value']+
                      '</p><script>document.getElementById("'+entry['Hash']+
                      '").onclick = function() {populateTransDetails('+
                      '"'+entry['Hash']+'")};</script>';
      $('#memTable').append(htmlInsert);
      htmlInsert = 'Current Mempool: '+mempoolcounter;
      $('#memTableHeader').empty();
      $('#memTableHeader').append(htmlInsert);
    })
  });
}



function populateTransDetails(transHash) {
  $('#transactionExplorerTable').empty();
  $('#memTable p').removeClass('highLightedText');
  $('#blockExplorerTable p').removeClass('highLightedText');
  $('#'+transHash).addClass('highLightedText');

  let strArg = '';
  $.getJSON(transactionDBurl, function (data) {
    $.each(data, function (index, tran) {
      if ( tran['Hash'] === transHash ) {  
        $.each(tran,function(key,value){
          strArg += key+': '+value+'<pre>';
        })
        $('#transactionExplorerTable').append(strArg);
      }
    })
      // if its not in the mempool, go looking in the blockchain
    if (strArg === ''){ searchChainForTrans() }
  });

    // Each transaction's data is a json object inside its block 
    // each block is a JSON object in the chain, so nesting..
  function searchChainForTrans(){    
    $.getJSON(blockChainurl, function (data) {
      $.each(data.reverse(), function (index, entry) {
        $.each(entry, function (key, trans) {
          if ( key === 'Transaction Data' ) {  
            $.each(trans, function (a, b){ 
              if (b['Hash'] === transHash){
                $.each(b,function(i,j){
                  strArg += i+': '+j+'<pre>';
                })
                $('#transactionExplorerTable').append(strArg);
                return 0;
              } // console.log(b['Value']);
            })  // TODO: I think this keeps looking after it finds until
          }     // its been though the whole chain.
        })
      })
    });
  }
}