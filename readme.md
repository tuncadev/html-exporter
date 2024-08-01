# HTML Exporter Plugin

HTML Exporter is a WordPress plugin that allows you to export pages, posts, or other custom post types to HTML and save them to the local uploads folder with additional functionalities. This plugin is currently in development.

## Features

- Export posts, pages, and custom post types to HTML.
- Schedule regular exports with various intervals (minutes for testing, weekly, monthly).
- Email notification with the download link upon export completion.
- View previous exports and download the generated HTML files.
- Manage scheduled exports: create, edit, delete.

## Installation

1. Clone this repository to your WordPress plugins directory:
    ```sh
    git clone https://github.com/tuncadev/html-exporter wp-content/plugins/html-exporter
    ```

2. Activate the plugin through the 'Plugins' menu in WordPress.

## Usage

1. **Settings Page**: Configure the post types to export, set the export interval, and provide an email for notifications.
2. **Export HTML Page**: Manually trigger an export.
3. **Previous Exports Page**: View and download previous exports.
4. **Scheduled Exports Page**: Manage scheduled export tasks (currently only accessible to the administrator who created the task).

## Development

This plugin is currently in development. Here are the planned features and improvements:

- Improve security and error handling.
- Enhance the admin interface for better user experience.
- Add detailed logging and debugging information.
- Optimize performance for large websites.

## Contributing

Contributions are welcome! If you would like to contribute, please fork the repository and submit a pull request.

## License

This plugin is open-source and licensed under the MIT License.

---

*Note: This plugin is in the development stage. Use it with caution and test thoroughly before deploying on a live site.*
