# wxy-editor

Content editor for wxy

# Configuration

```
// shared with wxy-single-auth, FALSE to not require any auth
$config['auth-user'] = 'myusername'; // FALSE by default

// template file to use with twig
$config['editor-template'] = 'editor.html';
// Note: if editor.html exists in the theme, it will be used,
//       which may be good, or not ...
```

# Dependency

You may want to use this plugin with `wxy-single-auth` or a similar authentication plugin
that provides `user` in the session when the user is logged in.

# Using

* [Icomoon](https://icomoon.io/) - custom icon fonts
* [Editor](https://github.com/lepture/editor) - markdown editor
* [Marked](https://github.com/chjj/marked) - markdown parser

# License

MIT
