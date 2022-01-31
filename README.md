# Anderson Chain

## Introduction

The Anderson Chain and associated crypto currency tokens (hereafter referred to as "marks")
Is a simulation of a complete crypto currency system ... blah

### Server Hosting

The Anderson Chain is hosted on UNE's 'Turing' server accessible at:
<https://turing.une.edu.au/~mander53/AndersonChainClient/>  
Right now it's all working as 3 entirely separate servers running on one machine, each server having its own 2 .json files storing everything. (the blockChain and the mempool)  
Scaling up to many more servers would be a very simple task.  
The client is both a block explorer and wallet, so everything can be monitored and tested on one page. For ease of testing the automated mining can be turned on and off, and there is also an option to turn on/off automated random transaction generation.
The client is also hosted on turing but could be run from anywhere, it just sends _POST requests to the 3 servers on turing.

**A note on serverID:**  
There is a text file (no extension) in each server’s root directory with JUST the public key that mining rewards are allocated to. No quotation marks, or formatting of any kind, JUST the 64-digit wallet key.  
This is the only difference between servers and is used for several things, not just mining rewards.
It is essential.

## Blockchain, Mining, Proof of work

Wall of text goes here



Each block consists of:

| Key           | Type          | Example           |
| ------------- | ------------- | ----------------- |
| Hash          | String (Hash) | "000046a6fa18..." |
| Miner         | String (Hash) | "5275ea582875..." |
| Index         | Int           | 6                 |
| PreviousHash  | String (Hash) | "27f7188a7010..." |
| Nonce         | Int           | 31774             |
| Coinbase      | Float         | 12.50             |
| Timestamp     | Float         | 1643204369.4139   |
| Fees          | Float         | 0.13              |
| TransactionData | Array [json objects] | [{"Hash":"c35f..},{"Hash":"c35f..}] |
| TransactionHashes | Array [hash strings]  | ["c35f6...","c35f6..."]   |

(int and float types are converted to string when encoded to .json)

The Hash being generated from fields:

```PHP
$hash = hash('sha256',$nonce.$miner.$index.$previousHash.$timestamp.$dataStr);
```

Example of a block json object in the chain:

```JSON
{"Hash":"000046....","Miner":"5275ea....","Index":"1","PreviousHash":"27f71....","Nonce":"31774","Coinbase":"50","Timestamp":"1643204369.4139","Fees":"0","TransactionData":[{"Hash":"c35f63....","Sender":"Mining_Reward","Sender Balance":"0","Receiver":"5275ea....","Receiver Balance":"50","Value":"50","Fee":"0","Timestamp":"1643204369.415"}],"TransactionHashes":["c35f63...."]}
```

## Wallets and GUI



Each wallet consists of a 'private key' and a 'public key', the public key is simply a hash of the private key.
Like most crypto currencies, knowing the private key grants full control of that wallet, loosing/forgetting the private key renders that wallet unrecoverable.
Unlike most crypto currencies my system uses SHA256 for both 'proof-of-work' and for wallet keys. (others use Elliptic Curve Digital Signature Algorithm or ECDSA for keys) As I can use sha256 with JS and PHP without external libraries. I also chose to forgo the seperation of public keys and wallet 'addresses' and just left them as the same thing.

Throughout development it was essential to keep a database on the server of all generated wallets. This was suposed to be removed at the end of development, but I have chosen to keep it as it is used for the automactic transaction generator and makes normal transaction creation simplier.  
However, all other uses of it have been removed and disabling the database will only break the transaction creator.

## Transactions and the Mempool

Each server keeps a list of transactions waiting to be added to the block chain (referred to as the mempool).  
In the Anderson chain this is simply a .json file, a much more complex system accounting for distant servers, dynamic ping and server groups is used in bitcoin.  
I had started with the intention of synchronising mempools between servers, but late in the project after synchronising blockchains I better understood the scope and protentional bugs of such a task and decided to leave that feature out.  
Servers MUST have separate memory pools, and MUST have the ability to 'recycle' rejected or invalid blocks. The complexity of recycling would use tremendous bandwidth and/or CPU time if servers could share some, but not all transactions.  
for eg. transaction validation currently does not involve searching the ENTIRE blockchain. If transactions could be in multiple mempools, we would need to check the entire chain for the existence of that transaction's Hash during mining (the entire chain, for every transaction).  

Each transaction consists of:

| Key        | Type          | Example             |
| ---------- | ------------- | ------------------- |
| Hash       | String (Hash) | "144f9fad6deb..."   |
| Sender     | String (Hash) | "e3f9de2fab8f..."   |
| Receiver   | String (Hash) | "bd5aef5596a5..."   |
| Value      | Float         | 13                  |
| Fee        | Float         | 0.13                |
| Timestamp  | Float         | 1643204798.942423   |

(int and float types are converted to string when encoded to .json)

The Hash being generated from all other fields:

```PHP
$hash = hash('sha256', $sender.$receiver.$value.$fee.$timestamp);
```

Example of a transaction json object inside the mempool:

```JSON
{"Hash":"13377....","Sender":"bd5ae....","Receiver":"e3f9d....","Value":"13","Fee":"0.13","Timestamp":1643204798.942423}
```

Additional fields are calculated and added during mining,  
Example of a transaction json object inside the blockchain:

```JSON
{"Hash":"eff2....","Sender":"e3f9....","Sender Balance":"10.85","Receiver":"bd5ae....","Receiver Balance":"27.85","Value":"15","Fee":"0.15","Timestamp":"1643204188.5625"}
```

No validation is preformed upon transaction creation or addition to a servers mempool. It is entirely possible (and frequent in testing) for transactions in the mempool to be invalid for eg. a person with a balance of 3 marks trying to send someone 13 marks.  
Validation must be done in block mining to maintain chain integrity.  
**Additional** validation such as balance checking, or receiver ID verification would be handled client side by a user wallet app. My GUI did have such validation during development, but all balance functions were converted to server-side for mining.

The transaction timestamp is used briefly in sorting which transactions will go in each block.  
It is NOT used to retrieve a balance, is was designed to but that created one hell of bug that took days to find.  
The block timestamp plus the transactions index within that block is used for balance retrieval functions.

### Theory and Project references

Alongside developing the Anderson Chain, over the past 4 months I also completed two Udemy courses and the 'Naivecoin' Tutorial  
<https://lhartikk.github.io/jekyll/update/2017/07/15/chapter0.html>  
It was good to go through the Naivecoin tutorial and the first Udemy course together, to have a technical coding example done alongside the theory.

<https://www.udemy.com/course/blockchain-and-bitcoin-fundamentals/>  
This Udemy course looked very elementary on the surface, but there was some stuff touched on I didn’t know about, so it was worth going through.
I would recommend this one to all but experts in the area. Despite not being technical with no code at all, as a holistic explanation this short course fills in any small gaps in theory knowledge.

<https://www.udemy.com/course/build-your-blockchain-az/>  
While this Udemy course is significantly longer, its covering pretty much the same depth. The extra time is due mostly to it being accompanied by a practical example in Python.
I’m glad I looked at this course as I find python and the flask web framework a lot easier to deal with then the Typescript that Naivecoin uses.

On top of the python example from the second Udemy course I’ve also been using Naivecoin and its predecessor ‘NaiveChain’ as reference while coding my blockchain, and also just to clarify some of the more complex concepts I come across.

I also made use of <https://www.blockchain.com/explorer> during both theory and development.


TODO:

FINAL gui tweak with final removal of walletDB ( use the function in getBalance() and keep a list in memory for auto-trans)

Write-up:
 Blockchain, Mining, Proof of work
 Wallets, GUI, Simulation
 Introduction
write-up Polish
