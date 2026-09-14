# Veylune Phase 1 Legacy Archive

This directory is a recoverable quarantine, not an active source directory.

## Contents

- `orig/`: 41 files that previously used the `.orig` suffix inside `bin/` or the Veylune theme source tree.
- `dormant/`: one unreferenced PLP JavaScript controller and one unimported 352-line header SCSS partial.
- `temp/`: the local interaction-audit script that previously lived in `.codex-tmp`.

Every archived file retains its original repository-relative path below its archive category and uses a `.snapshot` suffix. No archived file is imported, compiled, autoloaded, or executed by the storefront.

## Recovery

The Phase 1.3 Git checkpoint is the recovery boundary. Restore a specific snapshot only through a reviewed Git change, remove the `.snapshot` suffix, and rerun the ownership, architecture, build, and governance gates. Do not copy an entire archive category back into active source.

## Guard

`bin/veylune-architecture-guard-audit` enforces:

- zero active `.orig` files;
- exactly 41 archived `.orig` snapshots;
- presence of both dormant source snapshots and the temporary audit snapshot;
- zero unimported active SCSS partials;
- no increase above the active `!important` ceiling;
- no increase in debt-named SCSS files;
- governed-public ownership for the custom category route.
