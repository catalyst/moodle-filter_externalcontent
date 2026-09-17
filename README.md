# moodle-filter_externalcontent
A Moodle filter plugin to visually highlight external URLs that match a configured list of domains.

## What it does

The filter emits CSS rules for configured highlights. Each rule matches rendered
elements whose `href`, `src` or `data` attribute starts with common URL prefixes
for configured domains (`https://`, `http://`, `//`) and applies an outline
(`outline: 2px solid <colour>`). Wildcard entries (`*.example.com`) use a
best-effort token match for subdomains. When a label is configured, the rule
draws an SVG lane inside the same outline so links and media share the same
label treatment.

You can configure any number of highlights, each with its own set of domains,
colours and label, so different groups of external sites can be flagged
differently.

## Installation

Install as `filter/externalcontent` in your Moodle codebase, then visit
**Site administration > Plugins > Filters > Manage filters** to enable it.

## Configuration

Highlights are managed as a list rather than through simple plugin settings,
since each highlight needs its own domains/colours/label.

Go to **Site administration > Plugins > Filters > Manage filters**, then click
the **Settings** icon next to "External content highlighter". The list of
highlights is shown directly on that settings page:

- **Add a new highlight** opens a form to create one.
- Each existing highlight can be **enabled/disabled**, **edited** or **deleted**
  from the list, without affecting the others.

(Add/edit still use their own separate page, since a settings page is a
single form and can't contain another form nested inside it.)

Display is permission-based using the standard capability
`filter/externalcontent:view` (managed via Moodle roles/permissions).

Each highlight has the following fields:

- **Name**: an internal label to help you identify the highlight in the list.
- **Enabled**: whether this highlight is currently active.
- **Domains to highlight**: one domain per line, e.g. `example.com`. Prefix an
  entry with `*.` to also match its subdomains, e.g. `*.example.com`.
- **Label text**: kept for compatibility with existing configurations.
- **Label background colour**: the label's background colour, also used for the
  box outline (default `#f0ad4e`).
- **Label text colour**: the label's text colour (default `#ffffff`).

If a URL matches more than one enabled highlight, multiple outline rules may
apply and standard CSS cascade/order decides the final appearance.
