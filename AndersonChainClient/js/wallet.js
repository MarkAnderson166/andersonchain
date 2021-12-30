
$(function() {
  $('#walletform').submit(function(e) {
    e.preventDefault();
    sendWalletRequest();
  });
});

//      sendWalletRequest() 
//  for creating new wallets

// send POST request with all form data
function sendWalletRequest() {
  remove_msg();
  
  $.ajax({
    url: newWalletUrl,
    method: 'POST',
    data: $('#walletform').serialize(),
    dataType: 'json',
    success: function(data) {

      $('#server_response').addClass('success');
      $('#server_response span').text('New Wallet: ' +data);
      populateDropdowns();
    },
    error: function(jqXHR) {
      try {
        var $e = JSON.parse(jqXHR.responseText);
        // display error
        $('#server_response').addClass('error');
        $('#server_response span').text('Error from server: ' +$e.error);
      }
      catch (error) {
        console.log('Could not parse JSON error message: ' +error);
      }
    }
  });
}


//      getAllBalances()
//  added just to monitor everything, testing only

function getAllBalances() {
  $('#balanceTable').empty();
    $.ajax({
      url: getBalanceUrl,
      method: 'POST',
      dataType: 'json',
      success: function(bal) {
        $('#balanceTable').append(bal);
      },
    });
}





/*  // below method gets entire chain and pool from server 
    // and +/- trans from every user's entire history.
    // -good for dev/testing, would get unusable quickly
    //  might end up being part of a seperate user wallet website

function getAllBalances() {

  $('#balanceTable').empty();
  $.getJSON(walletDBurl, function (data) {
    $.each(data, function (index, entry) {

      let name = entry['name'];
      let total = 0;
      
      $.getJSON(transactionDBurl, function (data) {
        $.each(data, function (index, tran) {
          if ( tran['Sender'] === entry['publicKey'] ) {  
            total = total-(parseFloat(tran['Value'])+parseFloat(tran['Fee']));
          }
          if ( tran['Receiver'] === entry['publicKey'] ) {  
            total = total+parseFloat(tran['Value']);
          }
        })
        $.getJSON(blockChainurl, function (data) {
          $.each(data.reverse(), function (index, obj) {
            if ( obj['Miner'] === entry['publicKey'] ) {  
              total = total+(parseFloat(obj['Coinbase'])+parseFloat(obj['Fees']));
            }
            $.each(obj, function (key, trans) {
              if ( key === 'Transaction Data' ) {  
                $.each(trans, function (a, b){ 
                  if ( b['Sender'] === entry['publicKey'] ) {  
                    total = total-(parseFloat(b['Value'])+parseFloat(b['Fee']));
                  }
                  if ( b['Receiver'] === entry['publicKey'] ) {  
                    total = total+parseFloat(b['Value']);
                  }
                })
              }
            })
          })
          $('#balanceTable').append((total.toFixed(3)).toString()+' : '+name+'<pre>');
        });
      });
    });
  })
}
*/
