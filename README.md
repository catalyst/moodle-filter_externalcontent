# moodle-filter_externalcontent
A Moodle filter plugin to visually highlight external URLs that match a configured list of domains.

## Installation

Install as `filter/externalcontent` in your Moodle codebase, then visit
**Site administration > Plugins > Filters > Manage filters** to enable it.

## Configuration

Go to **Site administration > Plugins > Filters > External content highlighter**:

- **Domains to highlight**: one domain per line, e.g. `example.com`. Prefix an
  entry with `*.` to also match its subdomains, e.g. `*.example.com`.
- **Show label**: whether to include the label inside the box.
- **Label text**: the text displayed in the label (default `External`).
- **Label background colour**: the label's background colour, also used for the
  box outline (default `#f0ad4e`).
- **Label text colour**: the label's text colour (default `#ffffff`).
