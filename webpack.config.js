// @important: requirements here
const front = require('./webpack/front_config.js');
const admin = require('./webpack/admin_config.js');

module.exports = [
    front,
    admin,
];
