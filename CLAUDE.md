# CLAUDE.md

Guidance for Claude Code working in this repository.

## The working tree is on the owner's build machine — edit over ssh

This repository is checked out on two machines and only one of them is real: the build
machine serves the local `.test` hosts and produces the artefacts that actually run. The
laptop clone is for reading. Read from whichever is convenient, but **write, build, test,
commit and push on the build machine over `ssh`**, then `git pull` on the laptop.

`npm`, `node` and `nvm` are absent from a non-interactive `ssh` PATH — prefix with
`export NVM_DIR="$HOME/.nvm"; . "$NVM_DIR/nvm.sh";`.

This repository is public, so the host is deliberately not named here. The address, and the
reason for the rule, are in the owner's `~/.claude/CLAUDE.md`. It stands until they say
otherwise.
