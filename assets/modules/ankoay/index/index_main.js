import Index from "vue/components/modules/ankoay/Index/Index.jsx";

import { setChildView } from "vue/helper/renderVue.js";
import { getConfig } from "./index_config.js";

function main() {
    getConfig().component = {};
    
    setChildView("#app_body_wrapper", Index, getConfig().component);
}

export { main };
