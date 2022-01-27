
# Anderson Chain

## Introduction

The Anderson Chain and associated crypto currency tokens (hereafter refered to as "marks")
Is a simulation of a complete crypto currency system ... blah

### A note on serverID

There is a text file (no extension) in each servers root directory with JUST the public key that mining rewards are allocated to. No quotation marks, or formatting of any kind, JUST the 64 digit wallet key.
This is the only difference between servers and is used for several things, not just mining rewards.
It is essential.

### Server Hosting

The Anderson Chain is hosted on UNE's 'Turing' server accessible at:
<https://turing.une.edu.au/~mander53/AndersonChainClient/>
Right now its all working as 3 entirely seperate servers running on one machine, each server having its own 2 .json files storing everything. (the blockChain and the mempool)
The client is both a block explorer and wallet, so everything can be monitored and tested on one page. For ease of testing the automated mining can be turned on and off, and there is also an option to turn on/off automated random transaction generation.


## Blockchain, Mining, Proof of work

Wall of text goes here

* Hash
* Miner
* Index
* PreviousHash
* Nonce
* Coinbase
* Timestamp
* Fees
* TransactionData
* TransactionHashes

## Wallets, GUI, Simulation

Unlike most cryto currencys that use muliple encryption algorithms, my system uses SHA256 for both 'proof-of-work' and for wallet keys. As there is no reason not too and I can use it with JS and PHP without external libraries.

## Transaction Creation, Mempool, and History

* Hash
* Sender
* Receiver
* Value
* Fee
* Timestamp

### Img link

<img src="Gant.PNG" alt="Gant Chat" width="1000"/>

### Theory and Project references

Alongside developing the Anderson Chain, over the past 4 months I also completed two Udemy courses and the 'Naivecoin' Tutorial <https://lhartikk.github.io/jekyll/update/2017/07/15/chapter0.html>
It was good to go through the Naivecoin tutorial and the first Udemy course together, to have a technical coding example done alongside the theory.

<https://www.udemy.com/course/blockchain-and-bitcoin-fundamentals/>
This Udemy course looked very elementary on the surface, but there was some stuff touched on I didn’t know about so it was worth going through.
I would reccomend this one to all but experts in the area. Dispite not being technical with no code at all, as a wholisitic explaination this short course fills in any small gaps in theory knowleadge.

<https://www.udemy.com/course/build-your-blockchain-az/>
While this Udemy course is significantly longer, its covering pretty much the same depth. The extra time is due mostly to it being accompanied by a practical example in Python.
I’m glad I looked at this course as I find python and the flask web framework a lot easier to deal with then the Typescript that Naivecoin uses.

On top of the python example from the second Udemy course I’ve also been using Naivecoin and its predecessor ‘NaiveChain’ as reference while coding my blockchain, and also just to clarify some of the more complex concepts I come across.

I also made use of <https://www.blockchain.com/explorer> during both theory and development.



We an use `hash()` style highlighting and code snippits:
For example

```PHP
hash('sha256', $password.$key['name'].$key['Timestamp'])
```

| Table        | Table     | Table         |
| ------------ | --------- | ------------- |
| thing1       | thing1    | 10/05/2021    |
|              | thing1    | 14/05/2021    |
| thing2       | thing2    | 21/05/2021    |
|              | thing2    | 25/05/2021    |

TODO:

gui tweaks for wallet changes
re-work key generation
this write-up -fucking huge problem.
