const http = require('http');
const fs = require('fs');
const path = require('path');
const { spawn } = require('child_process');

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

    // 2. Not a static file: hand the full request (headers, cookies, body) to Laravel via the PHP bridge
    const chunks = [];
    req.on('data', (chunk) => chunks.push(chunk));
    req.on('end', () => {
        const fail = (message) => {
            res.writeHead(500, { 'Content-Type': 'text/plain' });
            res.end('Laravel Proxy Processing Error:\n' + message);
        };

        const php = spawn('php', ['server-bridge.php'], { cwd: __dirname });
        const stdout = [];
        const stderr = [];

        php.stdout.on('data', (chunk) => stdout.push(chunk));
        php.stderr.on('data', (chunk) => stderr.push(chunk));
        php.on('error', (error) => fail(error.message));
        php.on('close', () => {
            let result;
            try {
                result = JSON.parse(Buffer.concat(stdout).toString());
            } catch (error) {
                return fail(Buffer.concat(stderr).toString() || Buffer.concat(stdout).toString());
            }

            const body = Buffer.from(result.body, 'base64');
            res.statusCode = result.status;
            for (const [name, values] of Object.entries(result.headers)) {
                if (['content-length', 'transfer-encoding'].includes(name.toLowerCase())) continue;
                res.setHeader(name, values);
            }
            if (result.cookies.length) res.setHeader('Set-Cookie', result.cookies);
            res.setHeader('Content-Length', body.length);
            res.end(body);
        });

        php.stdin.end(JSON.stringify({
            method: req.method,
            url: req.url,
            headers: req.headers,
            body: Buffer.concat(chunks).toString('base64')
        }));
    });
}).listen(PORT, () => {
    console.log(`Laravel app with static asset routing running at http://localhost:${PORT}`);
});
