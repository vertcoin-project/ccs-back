For each funding request, a 2-of-3 multisignature address is used. The `public-keys` directory contains the pubkeys used to generate each multisignature address. Each list is signed by its respective owner from the Vertcoin-CCS team.

To verify pubkeys, [import the GPG key of each signer](https://github.com/vertcoin-project/guix.sigs/tree/main/builder-keys).

`funding-addresses` contains the generated multisignature addresses. To verify a funding address, use Vertcoin-Core's `createmultisig` command. Note the line number and input the public key from each signer's `pubkeys` file at the same line number in the following order:

```
vertcoin-cli createmultisig 2 '["vertiond","KforG","alvie"]' bech32
```
