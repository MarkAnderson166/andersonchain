
var serverUrl = "https://turing.une.edu.au/~mander53/AndersonChainServer/"
//var serverUrl = "http://localhost/AndersonChainServer/"

var newWalletUrl =      serverUrl+"wallet.php";
var newTransactionUrl = serverUrl+"transaction.php";
var newBlockUrl =       serverUrl+"block.php";
var genesisUrl =        serverUrl+"genesis.php";
var statementUrl =      serverUrl+"statement.php";
var checkForUpdatesUrl= serverUrl+"checkForUpdates.php";

var walletDBurl =       serverUrl+"getWalletDB.php";
var transactionDBurl =  serverUrl+"getTransactionDB.php";
var getBalanceUrl =     serverUrl+"getBalance.php";

var blockChainurl =     serverUrl+"getBlockChain.php";


window.onload = function() {
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
    $(function() { setInterval(genTestTrans, 6000); });
    $(function() { setInterval(genTestMine, 40000); });



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
  // ^ above way uses less bandwidth, the actual checking is done server end.
  // v below way or something like it might be used if I completely seperate 
  //      wallet, client and miner into different websites.
  /*
  $.ajax(serverUrl+"walletDB.json", {
    type: 'HEAD',
    dataType: 'jsonp',
    success: function(d,r,xhr) {
      var oldWalletFileSize = walletFileSize;
      walletFileSize = xhr.getResponseHeader('Content-Length');
      if (oldWalletFileSize !== walletFileSize) {populateDropdowns();}
    }
  });
  $.ajax(serverUrl+"transactionDB.json", {
    type: 'HEAD',
    dataType: 'jsonp',
    success: function(d,r,xhr) {
      var oldTransFileSize = transFileSize;
      transFileSize = xhr.getResponseHeader('Content-Length');
      if (oldTransFileSize !== transFileSize) {
        populateMempool();
        getAllBalances();
      }
    }
  });
  $.ajax(serverUrl+"blockChain.json", {
    type: 'HEAD',
    dataType: 'jsonp',
    success: function(d,r,xhr) {
      var oldblockChainFileSize = blockChainFileSize;
      blockChainFileSize = xhr.getResponseHeader('Content-Length');
      if (oldblockChainFileSize !== blockChainFileSize){populateBlockHistory();}
    }
  });
  */


//      populateDropdowns()
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

        //jquery got weird cors errors with firefox/linux?
/*
  $.getJSON(walletDBurl, function(data) {
    $.each(data, function (key, entry) {
      let formattedString = entry.walletIndex+' : '+entry.name;
      $('.walletDropdown').append(new Option(formattedString, entry.publicKey));
    })
  });
  */
}


//      remove_msg()
// remove messages / colors displayed in the server response box

function remove_msg() {
    // This is from the cosc260 A3 starter code, by james bishop
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



//      genesisBlock()      
// clear entire chain, mempool and wallet database (bezos button)

$(function() {
  $('#Genesis').submit(function(e) {
    e.preventDefault();
    genesisBlock();
  });
});

function genesisBlock(){  
  $.ajax({
    url: genesisUrl,
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


//      genTestTrans()
//   generates a random transaction with a button or timer
//  (assuming everyones password is 'password')

$(function() {
  $('#balanceform').submit(function(e) {
    e.preventDefault();
    genTestTrans();
  });
});

function genTestTrans() {
  remove_msg();

  let $publicKeys = []
  $.getJSON(walletDBurl, function(data) {
    $.each(data, function (key, entry) {
      $publicKeys.push(entry.publicKey);
    })

    $sender   =   $publicKeys[Math.floor(Math.random() * $publicKeys.length)];
    $receiver =   $publicKeys[Math.floor(Math.random() * $publicKeys.length)];
      // careful, this while loop will do nasty things
      // if the genesis block ever starts with < 2 wallets
    while ($receiver == $sender){
      $receiver = $publicKeys[Math.floor(Math.random() * $publicKeys.length)];
    }
    $value    =  Math.floor(Math.random() * 10)+10;

    $.ajax({
      url: newTransactionUrl,
      method: 'POST',
      data: 'sender='+$sender+'&password=password&receiver='+$receiver+'&value='+$value+'&fee='+$value/100,
      dataType: 'json',
      success: function(data) {

        $('#server_response').addClass('success');
        $('#server_response span').text('Test transaction added');
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
  });
}


//         genTestMine() 
//   this just mines a block with a button or timer
//      -random miner selected

function genTestMine() {
  remove_msg();
  
  let $publicKeys = []
  $.getJSON(walletDBurl, function(data) {
    $.each(data, function (key, entry) {
      $publicKeys.push(entry.publicKey);
    })

    $miner = $publicKeys[Math.floor(Math.random() * $publicKeys.length)];
    
    $.ajax({
      url: newBlockUrl,
      method: 'POST',
      data: 'miner='+$miner,
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
  });
}