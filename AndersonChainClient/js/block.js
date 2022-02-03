
//         startMining() 
//   tell all servers to mine  (starts the race)
//  they DO NOT run constantly, they periodically get this 'start signal'

$(function() {
  $('#minerForm').submit(function(e) {
    e.preventDefault();
    startMining();
  });
});

function autoMiner(){
  if (autoMineToggle === 1){
    startMining();
  }
}

function startMining() {
  remove_msg();
  for ( var i = 1; i <=3; i++) {
    $.ajax({  
      url: "https://turing.une.edu.au/~mander53/turing"+i+"/block.php",
      method: 'POST',
      data: 'miner='+i,
      dataType: 'json',
      async: true,
      success: function(data) {
        $('#server_response').addClass('success');
        $('#server_response span').text(data);
        populateBlockHistory();
        populateMempool();
        getAllBalances();

      }, error: function(data) {
        populateBlockHistory();
        populateMempool();
        getAllBalances();
      }
    });
  }  
}


function populateBlockHistory() {
  $('#blockHistoryTable').empty();
  var blockHistorycounter = 0;
  $.getJSON(blockChainurl, function (data) {
    $.each(data.reverse(), function (key, entry) {
      blockHistorycounter++;
          // I need the arg being passed to the func to be dynamically generated
          // so this is a section of html, with an in-line javascript func call,
          // being inserted into the html from the .js file.... yuck
      let htmlInsert = '<p id="'+entry.Hash+'">'+
                      entry['Hash'].slice(0,24)+'.... Index: '+entry.Index+
                      '</p><script>document.getElementById("'+entry.Hash+
                      '").onclick = function() {populateBlockExplorer('+
                      '"'+entry.Hash+'")};</script>';
      $('#blockHistoryTable').append(htmlInsert);
      htmlInsert = 'Chain Length: '+blockHistorycounter;
      $('#blockHistoryTableHeader').empty();
      $('#blockHistoryTableHeader').append(htmlInsert);
    })
  });
}

function populateBlockExplorer(transHash) {
  $('#transactionExplorerTable').empty(); // this is for GUI layout reasons only
  $('#blockExplorerTable').empty();
  $('#blockHistoryTable p').removeClass('highLightedText');
  $('#'+transHash).addClass('highLightedText');

  $.getJSON(blockChainurl, function (data) {
    $.each(data, function (key, entry) {
      if ( entry.Hash === transHash ) {
        let strArg = '';
        $.each(entry,function(key,value){
          if (key !== 'TransactionData' && key !== 'TransactionHashes' ) {
            strArg += key+': '+value+'<pre>';
          }
        })
        $('#blockExplorerTable').append(strArg);
        $('#blockExplorerTable').append('Transactions: <pre>');
      }
    })

    $.each(data, function (key, entry) {
      if ( entry.Hash === transHash ) {
        $.each(entry,function(key,value){
          if (key == 'TransactionHashes') {
            let clickableTrans = ''
            $.each(value, function (a, transHash) {
              clickableTrans += '<p id="'+transHash+'">'+transHash+
                  '</p><script>document.getElementById("'+transHash+
                  '").onclick = function() {populateTransDetails('+'"'+
                  transHash+'")};</script>';
            })
            $('#blockExplorerTable').append(clickableTrans);
          }
        })
      }
    })
  });
}










/*
// send POST request with all form data
function sendMineRequest() {
  remove_msg();
  
  $.ajax({
    url: newBlockUrl,
    method: 'POST',
    data: $('#mineform').serialize(),
    dataType: 'json',
    success: function(data) {

      $('#server_response').addClass('success');
      $('#server_response span').text(data);
      populateBlockHistory();
      populateMempool();
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
*/