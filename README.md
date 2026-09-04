# moodle-filter_externalcontent
A Moodle filter plugin to visually highlight external URLs that match a configured list of domains.

## What it does

The filter scans rendered text for `<a href="...">` links. If a link's host matches
one of the domains configured in an enabled **highlight**, the link is wrapped,
together with a small coloured label (e.g. `External`), inside a single outlined box
(`outline: 2px solid <colour>; padding-right: 4px;`), so the label and the URL
appear as one highlighted block rather than two separate touching boxes.

You can configure any number of highlights, each with its own set of domains,
colours and label, so different groups of external sites can be flagged
differently (e.g. a red "Partner" box and a blue "Vendor" box).

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

Each highlight has the following fields:

- **Name**: an internal label to help you identify the highlight in the list.
- **Enabled**: whether this highlight is currently active.
- **Domains to highlight**: one domain per line, e.g. `example.com`. Prefix an
  entry with `*.` to also match its subdomains, e.g. `*.example.com`.
- **Label text**: the text displayed in the label (default `External`).
- **Label background colour**: the label's background colour, also used for the
  box outline (default `#f0ad4e`).
- **Label text colour**: the label's text colour (default `#ffffff`).

If a link's host matches more than one enabled highlight, the first matching
highlight (in the order they were created) is applied.
