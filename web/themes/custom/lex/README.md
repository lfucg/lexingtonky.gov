# Developing the City of Lexington theme

## Installation

`npm install`

## Building the Sass

Build Sass: `gulp build`

For development mode use the default command:  `gulp`.
It rebuilds when .scss files change.

However, when there is a scss compilation error, the
default gulp command quits. Next thing you know, you're furiously
refreshing your browser but not seeing any layout changes.

To recover from compilation errors: `while; do; gulp; sleep 5; done`

## Dependencies

The theme is built on top of the [US Web Design Standards](https://github.com/18F/web-design-standards)
which live in assets/.
