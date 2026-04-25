(function (window) {
    'use strict';

    if (!window || !window.arwpWebMcpRuntime) {
        return;
    }

    var config = window.arwpWebMcpRuntime;
    var tools = Array.isArray(config.tools) ? config.tools : [];
    var capability = window.navigator &&
        window.navigator.mcp &&
        typeof window.navigator.mcp.registerTool === 'function';

    if (!capability) {
        return;
    }

    tools.forEach(function (tool) {
        if (!tool || !tool.name) {
            return;
        }

        window.navigator.mcp.registerTool(tool.name, tool);
    });
}(window));
