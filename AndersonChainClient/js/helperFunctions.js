
var serverUrl = "https://turing.une.edu.au/~mander53/turing1/"
var serverUrl = "https://turing.une.edu.au/~mander53/turing2/"
var serverUrl = "https://turing.une.edu.au/~mander53/turing3/"
//var serverUrl = "http://localhost/AndersonChainServer/"


var newWalletUrl =      serverUrl+"wallet.php";
var newTransactionUrl = serverUrl+"transaction.php";
//var newBlockUrl =       serverUrl+"block.php";
var genesisUrl =        serverUrl+"genesis.php";
var statementUrl =      serverUrl+"statement.php";
var checkForUpdatesUrl= serverUrl+"checkForUpdates.php";
var walletDBurl =       serverUrl+"getWalletDB.php";
var transactionDBurl =  serverUrl+"getTransactionDB.php";
var getBalanceUrl =     serverUrl+"getBalance.php";
var blockChainurl =     serverUrl+"getBlockChain.php";

var autoMineToggle = 0;
var autoTranToggle = 0;

window.onload = function() {
  selectServer(1);
  populateDropdowns();
  populateMempool();
  populateBlockHistory();
  getAllBalances();
  $(document).ready(function() { $.ajaxSetup({ cache: false }); });
  $('#transactionExplorerTable').append("(click any transaction hash)");
  $('#blockExplorerTable').append("(click any block hash)");
};


$(function() { setInterval(checkForUpdates, 1800); });
// reminder, change server update interval if you chance this.

function checkForUpdates(){
  $.ajax({
    url: checkForUpdatesUrl,
    method: 'POST',
    dataType: "json",
    data: {'Timestamp': Math.floor(Date.now()/1000) },
    success: function(data) {
      if (data % 2 == 0){ populateDropdowns(); }
      if (data % 3 == 0){ populateMempool(); }
      if (data % 5 == 0){ populateBlockHistory(); }
      if (data > 1     ){ getAllBalances(); }
    },
  });
}

// ----------------    Auto  miner, tranaction generator ----------------------
$(function() { setInterval(genTestTrans, 2000); });
$(function() { setInterval(autoMiner, 30000); });

$(function() {
  $('#autoFormMine').submit(function(e) {
    e.preventDefault();
    if ( autoMineToggle === 0) {
      autoMineToggle = 1;
      $('#autoMineState').empty();
      $('#autoMineState').append(('&nbsp;&nbsp;ON'));
    }
    else if ( autoMineToggle === 1) {
      autoMineToggle = 0;
      $('#autoMineState').empty();
      $('#autoMineState').append(('&nbsp;&nbsp;OFF'));
    }
  });
});

$(function() {
  $('#autoFormTran').submit(function(e) {
    e.preventDefault();
    if ( autoTranToggle === 0) {
      autoTranToggle = 1;
      $('#autoTranState').empty();
      $('#autoTranState').append(('&nbsp;&nbsp;ON'));
    }
    else if ( autoTranToggle === 1) {
      autoTranToggle = 0;
      $('#autoTranState').empty();
      $('#autoTranState').append(('&nbsp;&nbsp;OFF'));
    }
  });
});


// --------------------------------   Server selector    ----------------
// 

$(function() {
  $('#serverSelect1').submit(function(e) {
    e.preventDefault();
    selectServer(1);
  });
});
$(function() {
  $('#serverSelect2').submit(function(e) {
    e.preventDefault();
    selectServer(2);
  });
});
$(function() {
  $('#serverSelect3').submit(function(e) {
    e.preventDefault();
    selectServer(3);
  });
});

function selectServer(choice) {
  serverUrl = "https://turing.une.edu.au/~mander53/turing"+choice+"/"
  newWalletUrl =      serverUrl+"wallet.php";
  newTransactionUrl = serverUrl+"transaction.php";
  genesisUrl =        serverUrl+"genesis.php";
  statementUrl =      serverUrl+"statement.php";
  checkForUpdatesUrl= serverUrl+"checkForUpdates.php";
  walletDBurl =       serverUrl+"getWalletDB.php";
  transactionDBurl =  serverUrl+"getTransactionDB.php";
  getBalanceUrl =     serverUrl+"getBalance.php";
  blockChainurl =     serverUrl+"getBlockChain.php";

  populateMempool();
  populateBlockHistory();
  $('#serverSelectorHeader').empty();
  $('#serverSelectorHeader').append(('Selected Server: Turing'+choice));
}


//  -------------------------   populateDropdowns() ----------------
//   ease of use GUI element,
//   needs to change to allow manual key entry after wallet changes

function populateDropdowns() {
  $('.walletDropdown').empty();
  $('.walletDropdown').prop('selectedIndex', 0);
  $.ajax({
    url: walletDBurl,
    method: 'POST',
    dataType: 'json',
    success: function(data) {
      $.each(data, function (key, entry) {
        let formattedString = entry.walletIndex+' : '+entry.name;
        $('.walletDropdown').append(new Option(formattedString, entry.publicKey));
      })
    },
  });
}


//   --------------------------------   remove_msg()  ----------------
// remove messages / colors displayed in the server response box

function remove_msg() {
  var $server_response = $('#server_response');
  if ($server_response.hasClass('fade')) {
    $server_response.removeClass('fade');
  }
  if ($server_response.hasClass('success')) {
    $server_response.removeClass('success');
  }
  if ($server_response.hasClass('error')) {
    $server_response.removeClass('error');
  }
  $('#server_response span').text('');
}






// ----- Functions for Testing only ---------  
// ===========================================================================

//    generateStatement()
// dumps a text file per wallet to server containing every transaction.

$(function() {
  $('#Statement').submit(function(e) {
    e.preventDefault();
    generateStatement();
  });
});

function generateStatement(){  
  $.ajax({
    url: statementUrl,
    method: 'POST',
    data: '',
    dataType: 'json',
    success: function(data) {
      if(data > 0){
        $('#server_response').addClass('success fade');
        $('#server_response span').text('statements dumped to file (on server)')
      } 
    },
  });
}


//    'whoIs' button
//  input arg: wallet public key
//     looks up matching name in wallet db,
//     no version of this would be in the final version 

$(function() {
  $('#whoIsForm').submit(function(e) {
    e.preventDefault();
    remove_msg();

    $.getJSON(walletDBurl, function(data) {
      $.each(data, function (key, entry) {
        
        if ( entry.publicKey == ($('#whoIsForm').serialize()).slice(4,100)){
          $name = entry.name;
          $('#server_response').addClass('success');
          $('#server_response span').text('Name attached to entered key: '+$name);
          return false;
        } 
        $('#server_response').addClass('error');
        $('#server_response span').text('Enter a valid (used) public key');
      })
    });
  });
});



//   --------------------------------   genesisBlock()      ----------------
// clear entire chain, mempool and wallet database (bezos button)
//   on all 3 servers

$(function() {
  $('#Genesis').submit(function(e) {
    e.preventDefault();
    genesisBlock();
  });
});

function genesisBlock(){ 
  
  for ( var i = 1; i <=3; i++) {
    $.ajax({
      url: "https://turing.une.edu.au/~mander53/turing"+i+"/genesis.php",
      method: 'POST',
      data: '',
      dataType: 'json',
      success: function(data) {
        if(data > 0){
          $('#server_response').addClass('success fade');
          $('#server_response span').text('ran genesis function')
          populateDropdowns();
          populateMempool();
          populateBlockHistory();
          getAllBalances();
        } 
      },
    });
  }
}
