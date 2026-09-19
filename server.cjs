const http = require('http');
const fs = require('fs');
const path = require('path');
const { execSync } = require('child_process');

const PORT = 8000;

// Simple dictionary to handle asset file types properly
const mimeTypes = {
    '.html': 'text/html',
    '.css': 'text/css',
    '.js': 'text/javascript',
    '.json': 'application/json',
    '.png': 'image/png',
    '.jpg': 'image/jpeg',
    '.gif': 'image/gif',
    '.svg': 'image/svg+xml',
    '.ico': 'image/x-icon',
    '.mp3': 'audio/mpeg',
    '.wav': 'audio/wav'
};

http.createServer((req, res) => {
    // Strip query parameters to get the clean file path
    const urlPath = req.url.split('?')[0];
    const filePath = path.join(__dirname, 'public', urlPath);

    // 1. Check if the requested URL points to a real file in the /public directory
    if (fs.existsSync(filePath) && fs.statSync(filePath).isFile()) {
        const ext = path.extname(filePath).toLowerCase();
        const contentType = mimeTypes[ext] || 'application/octet-stream';

        res.writeHead(200, { 'Content-Type': contentType });
        return fs.createReadStream(filePath).pipe(res);
    }

    // 2. If it's not a static file, route it safely to Laravel's PHP engine
    try {
        const env = Object.assign({}, process.env);

        env.REQUEST_URI = req.url;
        env.REQUEST_METHOD = req.method;
        env.SERVER_NAME = 'localhost';
        env.SERVER_PORT = PORT.toString();
        env.HTTP_HOST = `localhost:${PORT}`;
        env.SCRIPT_FILENAME = path.join(__dirname, 'public/index.php');
        env.SCRIPT_NAME = '/index.php';

        // Forward the query string so public/index.php can populate $_GET (PHP CLI never does this itself)
        const queryIndex = req.url.indexOf('?');
        env.QUERY_STRING = queryIndex !== -1 ? req.url.slice(queryIndex + 1) : '';

        if (req.headers['user-agent']) env.HTTP_USER_AGENT = req.headers['user-agent'];
        if (req.headers['accept']) env.HTTP_ACCEPT = req.headers['accept'];

        const output = execSync(`php public/index.php`, { env: env, maxBuffer: 1024 * 1024 * 15 });

        // PHP CLI can't send headers, so sniff the body to return a sensible content type
        const trimmed = output.toString().trimStart();
        const contentType = (trimmed.startsWith('{') || trimmed.startsWith('[')) ? 'application/json' : 'text/html';

        res.writeHead(200, { 'Content-Type': contentType });
        res.end(output);
    } catch (error) {
        res.writeHead(500, { 'Content-Type': 'text/plain' });
        res.end("Laravel Proxy Processing Error:\n" + (error.stderr?.toString() || error.message));
    }
}).listen(PORT, () => {
    console.log(`Laravel app with static asset routing running at http://localhost:${PORT}`);
});
