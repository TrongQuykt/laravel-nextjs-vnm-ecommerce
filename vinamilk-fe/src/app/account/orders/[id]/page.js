"use client";
"use strict";
var __assign = (this && this.__assign) || function () {
    __assign = Object.assign || function(t) {
        for (var s, i = 1, n = arguments.length; i < n; i++) {
            s = arguments[i];
            for (var p in s) if (Object.prototype.hasOwnProperty.call(s, p))
                t[p] = s[p];
        }
        return t;
    };
    return __assign.apply(this, arguments);
};
var __awaiter = (this && this.__awaiter) || function (thisArg, _arguments, P, generator) {
    function adopt(value) { return value instanceof P ? value : new P(function (resolve) { resolve(value); }); }
    return new (P || (P = Promise))(function (resolve, reject) {
        function fulfilled(value) { try { step(generator.next(value)); } catch (e) { reject(e); } }
        function rejected(value) { try { step(generator["throw"](value)); } catch (e) { reject(e); } }
        function step(result) { result.done ? resolve(result.value) : adopt(result.value).then(fulfilled, rejected); }
        step((generator = generator.apply(thisArg, _arguments || [])).next());
    });
};
var __generator = (this && this.__generator) || function (thisArg, body) {
    var _ = { label: 0, sent: function() { if (t[0] & 1) throw t[1]; return t[1]; }, trys: [], ops: [] }, f, y, t, g = Object.create((typeof Iterator === "function" ? Iterator : Object).prototype);
    return g.next = verb(0), g["throw"] = verb(1), g["return"] = verb(2), typeof Symbol === "function" && (g[Symbol.iterator] = function() { return this; }), g;
    function verb(n) { return function (v) { return step([n, v]); }; }
    function step(op) {
        if (f) throw new TypeError("Generator is already executing.");
        while (g && (g = 0, op[0] && (_ = 0)), _) try {
            if (f = 1, y && (t = op[0] & 2 ? y["return"] : op[0] ? y["throw"] || ((t = y["return"]) && t.call(y), 0) : y.next) && !(t = t.call(y, op[1])).done) return t;
            if (y = 0, t) op = [op[0] & 2, t.value];
            switch (op[0]) {
                case 0: case 1: t = op; break;
                case 4: _.label++; return { value: op[1], done: false };
                case 5: _.label++; y = op[1]; op = [0]; continue;
                case 7: op = _.ops.pop(); _.trys.pop(); continue;
                default:
                    if (!(t = _.trys, t = t.length > 0 && t[t.length - 1]) && (op[0] === 6 || op[0] === 2)) { _ = 0; continue; }
                    if (op[0] === 3 && (!t || (op[1] > t[0] && op[1] < t[3]))) { _.label = op[1]; break; }
                    if (op[0] === 6 && _.label < t[1]) { _.label = t[1]; t = op; break; }
                    if (t && _.label < t[2]) { _.label = t[2]; _.ops.push(op); break; }
                    if (t[2]) _.ops.pop();
                    _.trys.pop(); continue;
            }
            op = body.call(thisArg, _);
        } catch (e) { op = [6, e]; y = 0; } finally { f = t = 0; }
        if (op[0] & 5) throw op[1]; return { value: op[0] ? op[1] : void 0, done: true };
    }
};
Object.defineProperty(exports, "__esModule", { value: true });
exports.default = OrderDetailPage;
var react_1 = require("react");
var navigation_1 = require("next/navigation");
var api_1 = require("@/lib/api");
var framer_motion_1 = require("framer-motion");
var lucide_react_1 = require("lucide-react");
var link_1 = require("next/link");
function OrderDetailPage() {
    var _this = this;
    var _a;
    var params = (0, navigation_1.useParams)();
    var router = (0, navigation_1.useRouter)();
    var _b = (0, react_1.useState)(null), order = _b[0], setOrder = _b[1];
    var _c = (0, react_1.useState)(true), loading = _c[0], setLoading = _c[1];
    var _d = (0, react_1.useState)(true), isItemsExpanded = _d[0], setIsItemsExpanded = _d[1];
    var _e = (0, react_1.useState)({}), collapsedPackages = _e[0], setCollapsedPackages = _e[1];
    var togglePackage = function (pkgNum) {
        setCollapsedPackages(function (prev) {
            var _a;
            return (__assign(__assign({}, prev), (_a = {}, _a[pkgNum] = !prev[pkgNum], _a)));
        });
    };
    (0, react_1.useEffect)(function () {
        fetchOrderDetail();
    }, [params.id]);
    var fetchOrderDetail = function () { return __awaiter(_this, void 0, void 0, function () {
        var res, e_1;
        return __generator(this, function (_a) {
            switch (_a.label) {
                case 0:
                    _a.trys.push([0, 2, 3, 4]);
                    return [4 /*yield*/, api_1.checkoutApi.getOrderDetail(params.id)];
                case 1:
                    res = _a.sent();
                    setOrder(res.data || res);
                    return [3 /*break*/, 4];
                case 2:
                    e_1 = _a.sent();
                    console.error("Failed to fetch order detail", e_1);
                    return [3 /*break*/, 4];
                case 3:
                    setLoading(false);
                    return [7 /*endfinally*/];
                case 4: return [2 /*return*/];
            }
        });
    }); };
    var getStatusDisplay = function (status) {
        switch (status) {
            case "pending":
                return { label: "Chờ tiếp nhận", color: "text-amber-600 bg-amber-50", icon: lucide_react_1.Clock };
            case "processing":
                return { label: "Chờ đóng gói", color: "text-blue-600 bg-blue-50", icon: lucide_react_1.Package };
            case "packed":
                return { label: "Đã đóng gói", color: "text-teal-600 bg-teal-50", icon: lucide_react_1.Package };
            case "shipping":
                return { label: "Đang giao hàng", color: "text-indigo-600 bg-indigo-50", icon: lucide_react_1.Truck };
            case "ready_for_pickup":
                return { label: "Sẵn sàng nhận", color: "text-cyan-600 bg-cyan-50", icon: lucide_react_1.MapPin };
            case "completed":
                return { label: "Đã hoàn tất", color: "text-green-600 bg-green-50", icon: lucide_react_1.CheckCircle };
            case "cancelled":
                return { label: "Đã hủy", color: "text-red-600 bg-red-50", icon: lucide_react_1.XCircle };
            default:
                return { label: status, color: "text-gray-600 bg-gray-50", icon: lucide_react_1.HelpCircle };
        }
    };
    if (loading)
        return <div className="h-96 bg-gray-50 animate-pulse rounded-3xl"/>;
    if (!order)
        return <div className="p-10 text-center font-black text-[#002094]">Không tìm thấy đơn hàng</div>;
    var status = getStatusDisplay(order.status);
    var StatusIcon = status.icon;
    // Group items by package_number
    var packages = (_a = order.items) === null || _a === void 0 ? void 0 : _a.reduce(function (groups, item) {
        var pkgNum = item.package_number || "".concat(order.order_number, "FN1");
        if (!groups[pkgNum])
            groups[pkgNum] = [];
        groups[pkgNum].push(item);
        return groups;
    }, {});
    // Compute subtotal across non-gift packages to allocate discounts proportionally
    var packageKeys = Object.keys(packages || {});
    var pkgSubtotals = {};
    var overallSubtotal = 0;
    packageKeys.forEach(function (k) {
        var items = packages[k] || [];
        var subtotal = items.reduce(function (s, it) { return s + Number(it.total || 0); }, 0);
        pkgSubtotals[k] = subtotal;
        overallSubtotal += subtotal;
    });
    var totalDiscount = Number(order.discount_amount || 0);
    // allocate discounts proportionally across packages (by subtotal)
    var pkgDiscounts = {};
    if (overallSubtotal > 0 && totalDiscount > 0) {
        var allocated_1 = 0;
        var keys = Object.keys(pkgSubtotals);
        keys.forEach(function (k, i) {
            var share = pkgSubtotals[k] / overallSubtotal;
            var amt = Math.round(share * totalDiscount);
            pkgDiscounts[k] = amt;
            allocated_1 += amt;
        });
        // adjust rounding remainder on last package
        var remainder = totalDiscount - allocated_1;
        if (remainder !== 0) {
            var lastKey = Object.keys(pkgDiscounts).reverse().find(function (k) { return pkgSubtotals[k] > 0; }) || Object.keys(pkgDiscounts)[0];
            if (lastKey)
                pkgDiscounts[lastKey] = (pkgDiscounts[lastKey] || 0) + remainder;
        }
    }
    else {
        Object.keys(pkgSubtotals).forEach(function (k) { return pkgDiscounts[k] = 0; });
    }
    var shippingCost = Number(order.shipping_cost || 0);
    var orderSubtotal = overallSubtotal;
    var orderTotal = Math.max(0, orderSubtotal + shippingCost - totalDiscount);
    var hasOrderDiscount = totalDiscount > 0;
    var renderStepper = function () {
        var _a;
        var status = order.status;
        var steps = [
            { key: "ordered", label: "Đơn đã đặt" },
            { key: "pending", label: "Chờ tiếp nhận" },
            { key: "processing", label: "Chờ đóng gói" },
            { key: "packed", label: "Đã đóng gói" },
            { key: "shipping", label: "Đang giao hàng" },
            { key: "completed", label: "Hoàn tất" }
        ];
        var statusHierarchy = {
            pending: 1,
            processing: 2,
            packed: 3,
            shipping: 4,
            completed: 5,
            failed: 5,
            cancelled: 0
        };
        var currentStepIndex = (_a = statusHierarchy[status]) !== null && _a !== void 0 ? _a : 1;
        if (status === "cancelled") {
            return (<div className="flex items-center justify-center p-6 bg-red-50 border border-red-100 rounded-3xl gap-4 shadow-sm mb-8 w-full select-none text-red-700">
          <lucide_react_1.XCircle size={28} className="text-red-500 animate-bounce"/>
          <div>
            <h3 className="font-extrabold text-[16px] text-red-800">Đơn hàng đã bị hủy</h3>
            <p className="text-xs text-red-500 font-semibold mt-0.5">Chúng tôi đã hủy đơn hàng theo yêu cầu hoặc do sự cố giao nhận.</p>
          </div>
        </div>);
        }
        var finalLabel = status === "failed" ? "Giao hàng thất bại" : "Giao thành công";
        return (<div className="flex items-center justify-between w-full p-6 bg-transparent border border-blue-200 rounded-2xl gap-2 mb-8 select-none overflow-x-auto">
        {steps.map(function (step, idx) {
                var _a;
                var isActive = false;
                var isCompleted = false;
                if (step.key === "ordered") {
                    isCompleted = true;
                }
                else if (step.key === "completed") {
                    if (status === "completed" || status === "failed") {
                        isActive = true;
                        isCompleted = true;
                    }
                }
                else {
                    var stepIndex = (_a = statusHierarchy[step.key]) !== null && _a !== void 0 ? _a : 0;
                    if (currentStepIndex > stepIndex) {
                        isCompleted = true;
                    }
                    else if (currentStepIndex === stepIndex) {
                        isActive = true;
                    }
                }
                var label = step.key === "completed" ? finalLabel : step.label;
                var isFailedStep = step.key === "completed" && status === "failed";
                var isSuccessStep = step.key === "completed" && status === "completed";
                var colorClass = isFailedStep
                    ? "text-red-600 font-black"
                    : isSuccessStep
                        ? "text-green-600 font-black"
                        : isCompleted
                            ? "text-[#002094] font-black"
                            : isActive
                                ? "text-blue-500 font-bold"
                                : "text-gray-300 font-medium";
                var bgClass = isFailedStep
                    ? "bg-red-50 text-red-500 border-red-200"
                    : isSuccessStep
                        ? "bg-green-50 text-green-500 border-green-200"
                        : isCompleted
                            ? "bg-blue-50 text-[#002094] border-blue-200"
                            : isActive
                                ? "bg-blue-50/50 text-blue-500 border-blue-200 animate-pulse"
                                : "bg-gray-50 text-gray-300 border-gray-100";
                return (<react_1.default.Fragment key={step.key}>
              {idx > 0 && (<div className={"flex-grow h-1 mx-2 rounded-full ".concat(isCompleted ? "bg-[#002094]" : "bg-gray-100", " transition-all duration-500")}/>)}
              <div className="flex flex-col items-center text-center gap-2 flex-shrink-0">
                <div className={"w-12 h-12 rounded-full border-2 ".concat(bgClass, " flex items-center justify-center shadow-sm transition-all duration-300")}>
                  {step.key === "ordered" && <lucide_react_1.Package size={22}/>}
                  {step.key === "pending" && <lucide_react_1.Clock size={22}/>}
                  {step.key === "processing" && <lucide_react_1.Package size={22}/>}
                  {step.key === "packed" && <lucide_react_1.Package size={22}/>}
                  {step.key === "shipping" && <lucide_react_1.Truck size={22}/>}
                  {step.key === "completed" && (status === "failed" ? <lucide_react_1.XCircle size={22}/> : <lucide_react_1.CheckCircle size={22}/>)}
                </div>
                <span className={"text-[12px] tracking-tight ".concat(colorClass)}>{label}</span>
              </div>
            </react_1.default.Fragment>);
            })}
      </div>);
    };
    return (<div className="space-y-6 pb-20 font-sans text-[#002094]">
      {/* Header */}
      <div className="space-y-1">
        <h2 className="text-[28px] font-bold">
          Đơn hàng #{order.order_number}
        </h2>
        <div className="flex items-center gap-2 text-[14px] text-gray-500 font-medium">
          <lucide_react_1.Clock size={14}/>
          <span>{new Date(order.created_at).toLocaleDateString('vi-VN', { day: '2-digit', month: '2-digit', year: 'numeric' })}</span>
        </div>
      </div>

      {/* Progress Stepper Timeline */}
      {renderStepper()}

      {/* Tabs */}
      <div className="border-b border-gray-200">
        <button className="px-4 py-3 text-[14px] font-bold bg-[#E9EDF5] border-b-2 border-[#002094]">
          Thông tin đơn hàng
        </button>
      </div>

      {/* Render Packages */}
      <div className="space-y-6">
        {packages && Object.entries(packages).map(function (_a, idx) {
            var _b, _c, _d, _e, _f, _g, _h, _j, _k, _l, _m, _o, _p, _q, _r, _s;
            var pkgNum = _a[0], items = _a[1];
            var isGiftPkg = pkgNum.includes('SXU') || pkgNum.includes('GIFT');
            var pkgTotal = items.reduce(function (sum, i) { return sum + Number(i.total); }, 0);
            // find scheduled date from care subscription deliveries if present
            var scheduledDate = null;
            if (order.order_type === 'care' && order.care_subscription && order.care_subscription.deliveries) {
                var del = order.care_subscription.deliveries.find(function (d) { return Number(d.delivery_index) === idx + 1; });
                if (del)
                    scheduledDate = del.scheduled_date;
            }
            return (<div key={pkgNum} className="bg-transparent border border-blue-100 rounded-xl overflow-hidden shadow-sm">
              <div className="p-6 space-y-6">
                {/* Package Header */}
                <div onClick={function () { return togglePackage(pkgNum); }} className="flex items-center justify-between cursor-pointer select-none hover:opacity-85 transition-opacity">
                    <div className="flex items-center gap-3">
                    <div>
                      <h3 className="text-[18px] font-bold">Kiện hàng {idx + 1}</h3>
                      {scheduledDate && (<div className="text-sm text-gray-500">{new Date(scheduledDate).toLocaleDateString('vi-VN')}</div>)}
                    </div>
                    <span className={"px-2 py-0.5 rounded text-[11px] font-bold uppercase ".concat(status.color)}>
                      {status.label}
                    </span>
                  </div>
                    <div className="flex items-center gap-4">
                    <div className="text-right">
                      <div className="text-xs text-gray-400">Tổng kiện hàng</div>
                      <span className="text-[18px] font-bold">
                        {isGiftPkg ? '0đ' : "".concat(pkgTotal.toLocaleString(), "\u0111")}
                      </span>
                    </div>
                    <button className="p-1 hover:bg-[#002094]/5 rounded-full transition-colors">
                      {collapsedPackages[pkgNum] ? (<lucide_react_1.ChevronDown size={20} className="text-[#002094]"/>) : (<lucide_react_1.ChevronUp size={20} className="text-[#002094]"/>)}
                    </button>
                  </div>
                </div>

                <div className="text-[14px]">
                  <p className="text-[#002094] font-black">Mã kiện hàng #{pkgNum}</p>
                </div>

                <framer_motion_1.AnimatePresence initial={false}>
                  {!collapsedPackages[pkgNum] && (<framer_motion_1.motion.div initial={{ height: 0, opacity: 0 }} animate={{ height: "auto", opacity: 1 }} exit={{ height: 0, opacity: 0 }} transition={{ duration: 0.2 }} className="overflow-hidden">
                      <div className="space-y-8 pt-4 border-t border-gray-50">

                        {/* Payment & Delivery */}
                        <div className="grid grid-cols-1 gap-6">
                          <div className="space-y-1">
                            <p className="text-[11px] font-bold text-gray-400 uppercase tracking-wider">Phương thức thanh toán</p>
                            <p className="text-[15px] font-bold">
                              {order.payment_method === 'cod' ? 'Thanh toán khi nhận hàng (COD)' :
                        order.payment_method === 'momo' ? 'Ví điện tử MoMo' :
                            order.payment_method === 'vnpay' ? 'Cổng thanh toán VNPay' :
                                order.payment_method === 'stripe' ? 'Thẻ quốc tế (Stripe)' :
                                    order.payment_method === 'paypal' ? 'Thẻ quốc tế (PayPal)' :
                                        "Th\u1EBB qu\u1ED1c t\u1EBF (".concat(String(order.payment_method || '').toUpperCase(), ")")}
                            </p>
                          </div>

                          <div className="space-y-1">
                            <p className="text-[11px] font-bold text-gray-400 uppercase tracking-wider">Phương thức giao nhận</p>
                            <p className="text-[15px] font-bold text-blue-800">
                              {order.delivery_type === 'pickup' ? 'Nhận tại cửa hàng' : (order.shipping_method_name || 'Giao hàng tiêu chuẩn')}
                            </p>

                          </div>

                          <div className="space-y-1">
                            <p className="text-[11px] font-bold text-gray-400 uppercase tracking-wider">
                              {order.delivery_type === 'pickup' ? 'Thông tin cửa hàng & nhận hàng' : 'Địa chỉ nhận hàng'}
                            </p>
                            <div className="text-[15px] font-bold space-y-0.5">
                              {order.delivery_type === 'pickup' ? (<div className="space-y-2">
                                  <div className="p-3 bg-blue-50/50 border border-blue-100 rounded-lg">
                                    <p className="text-blue-900 font-black">{((_b = order.store) === null || _b === void 0 ? void 0 : _b.name) || 'Cửa hàng Vinamilk'}</p>
                                    <p className="text-xs text-gray-600 font-medium mt-0.5">
                                      {((_c = order.store) === null || _c === void 0 ? void 0 : _c.address) || 'Địa chỉ cửa hàng'}, {((_d = order.store) === null || _d === void 0 ? void 0 : _d.ward) || ''}, {((_e = order.store) === null || _e === void 0 ? void 0 : _e.district) || ''}, {((_f = order.store) === null || _f === void 0 ? void 0 : _f.province) || ''}
                                    </p>
                                  </div>
                                  <div className="text-xs space-y-1 text-blue-900 bg-blue-50/50 p-3 rounded-lg border border-blue-100">
                                    <p><b>Người nhận:</b> {((_g = order.shipping_address) === null || _g === void 0 ? void 0 : _g.name) || 'Chưa cập nhật'} • {((_h = order.shipping_address) === null || _h === void 0 ? void 0 : _h.phone) || '-'}</p>
                                    <p><b>Thời gian hẹn:</b> <span className="text-blue-900 font-bold">{order.pickup_time || '-'}</span></p>
                                  </div>
                                </div>) : (<>
                                  <p>{((_j = order.shipping_address) === null || _j === void 0 ? void 0 : _j.name) || (((_k = order.shipping_address) === null || _k === void 0 ? void 0 : _k.last_name) + ' ' + ((_l = order.shipping_address) === null || _l === void 0 ? void 0 : _l.first_name))} • {(_m = order.shipping_address) === null || _m === void 0 ? void 0 : _m.phone}</p>
                                  <p className="text-gray-500 font-medium">
                                    {((_o = order.shipping_address) === null || _o === void 0 ? void 0 : _o.detail) || ((_p = order.shipping_address) === null || _p === void 0 ? void 0 : _p.address)}, {(_q = order.shipping_address) === null || _q === void 0 ? void 0 : _q.ward}, {(_r = order.shipping_address) === null || _r === void 0 ? void 0 : _r.district}, {(_s = order.shipping_address) === null || _s === void 0 ? void 0 : _s.city}
                                  </p>
                                </>)}
                            </div>
                          </div>

                          {order.invoice_info && (<div className="space-y-1 pt-4 border-t border-gray-100">
                              <p className="text-[11px] font-bold text-red-500 uppercase tracking-wider">Thông tin xuất hóa đơn VAT</p>
                              <div className="text-[13px] bg-red-50/40 border border-red-100/60 p-4 rounded-lg space-y-1.5 mt-1 text-[#002060]">
                                <p><b>Loại hóa đơn:</b> <span className="px-2 py-0.5 bg-red-100 text-red-700 rounded text-xs font-black uppercase">{order.invoice_info.type === 'personal' ? 'Cá nhân' : 'Công ty / Đơn vị'}</span></p>
                                <p><b>{order.invoice_info.type === 'personal' ? 'Họ và tên:' : 'Tên công ty:'}</b> {order.invoice_info.name || '-'}</p>
                                <p><b>Mã số thuế:</b> {order.invoice_info.tax_code || '-'}</p>
                                <p><b>Địa chỉ:</b> {order.invoice_info.address || '-'}</p>
                                {order.invoice_info.phone && <p><b>Số điện thoại:</b> {order.invoice_info.phone}</p>}
                                {order.invoice_info.email && <p><b>Email nhận hóa đơn:</b> {order.invoice_info.email}</p>}
                              </div>
                            </div>)}
                        </div>

                        {/* Products Section */}
                        <div className="space-y-4">
                          <h4 className="text-[11px] font-bold text-gray-400 uppercase tracking-wider">
                            {isGiftPkg ? 'QUÀ KÈM THEO ĐƠN HÀNG' : 'SẢN PHẨM'}
                          </h4>

                          <div className={"space-y-4 ".concat(isGiftPkg ? 'bg-[#F9FFD9] p-4 rounded-lg' : '')}>
                            {items.map(function (item) { return (<div key={item.id} className="flex gap-4 items-center">
                                <div className="w-14 h-14 bg-white rounded flex-shrink-0 flex items-center justify-center p-1 border border-gray-100 relative">
                                  <img src={(0, api_1.getImageUrl)(item.image) || "/placeholder.png"} className="w-full h-full object-contain" alt={item.product_name}/>
                                  <span className="absolute -top-1.5 -right-1.5 bg-blue-100 text-[#002094] text-[10px] font-bold w-5 h-5 rounded-full flex items-center justify-center border border-white">
                                    {item.quantity}
                                  </span>
                                </div>
                                <div className="flex-grow min-w-0">
                                  <p className="text-[14px] font-bold line-clamp-1">{item.product_name}</p>
                                  <div className="flex gap-2 mt-0.5">
                                    {item.volume && <span className="text-[12px] text-gray-400 font-medium">{item.volume}</span>}
                                    {item.packing_type && <span className="text-[12px] text-gray-400 font-medium">{item.packing_type}</span>}
                                  </div>
                                </div>
                                <div className="text-right flex-shrink-0">
                                  {isGiftPkg ? (<span className="text-[14px] font-bold">Quà tặng</span>) : (<>
                                      <p className="text-[15px] font-bold">{Number(item.total).toLocaleString()}đ</p>
                                      {item.original_price > item.price && (<p className="text-[11px] text-gray-300 line-through font-bold">
                                          {Number(item.original_price * item.quantity).toLocaleString()}đ
                                        </p>)}
                                    </>)}
                                </div>
                              </div>); })}
                          </div>
                        </div>

                        {/* Individual Package Summary (Only if not gift) */}
                        {!isGiftPkg && (<div className="space-y-2 pt-6 border-t border-gray-50">
                            <div className="flex justify-between text-[14px]">
                              <span className="font-medium text-gray-500">Tổng tiền hàng</span>
                              <span className="font-bold">{pkgTotal.toLocaleString()}đ</span>
                            </div>
                            <div className="flex justify-between text-[14px]">
                              <span className="font-medium text-gray-500">Phí vận chuyển</span>
                              <span className="font-bold">{idx === 0 ? shippingCost.toLocaleString() : '0'}đ</span>
                            </div>
                            <div className="flex justify-between text-[16px] pt-2 border-t border-gray-50">
                              <span className="font-bold">Tổng</span>
                              <span className="font-bold">{(pkgTotal + (idx === 0 ? shippingCost : 0)).toLocaleString()}đ</span>
                            </div>
                          </div>)}

                        {isGiftPkg && (<div className="space-y-2 pt-6 border-t border-gray-50">
                            <div className="flex justify-between text-[14px]">
                              <span className="font-medium text-gray-500">Tổng</span>
                              <span className="font-bold">0đ</span>
                            </div>
                          </div>)}
                      </div>
                    </framer_motion_1.motion.div>)}
                </framer_motion_1.AnimatePresence>
              </div>
            </div>);
        })}
      </div>

      {/* Order Summary */}
      <div className="bg-white border border-blue-100 rounded-3xl p-6 shadow-sm">
        <h3 className="text-[16px] font-black text-[#001c9a] mb-4">Tóm tắt đơn hàng</h3>
        <div className="space-y-3 text-[14px]">
          <div className="flex justify-between text-gray-500">
            <span>Tổng tiền hàng</span>
            <span className="font-bold">{orderSubtotal.toLocaleString()}đ</span>
          </div>
          <div className="flex justify-between text-gray-500">
            <span>Phí vận chuyển</span>
            <span className="font-bold">{shippingCost.toLocaleString()}đ</span>
          </div>
          {hasOrderDiscount && (<div className="flex justify-between text-pink-500">
              <span>Voucher giảm giá</span>
              <span className="font-bold">-{totalDiscount.toLocaleString()}đ</span>
            </div>)}
          <div className="flex justify-between border-t border-gray-100 pt-3 text-[16px] font-black">
            <span>Tổng thanh toán</span>
            <span>{orderTotal.toLocaleString()}đ</span>
          </div>
        </div>
      </div>

      {/* Re-order Button */}
      <button className="w-full bg-[#0000A0] text-white py-4 rounded-lg font-bold text-[16px] hover:bg-blue-800 transition-all">
        Mua lại
      </button>

      {/* Support */}
      <div className="text-center text-[13px] text-gray-400 font-medium">
        Bạn cần hỗ trợ? <link_1.default href="/support" className="text-[#002094] font-bold">Liên hệ CSKH</link_1.default>
      </div>
    </div>);
}
