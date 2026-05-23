"use client";

import React, { useEffect, useState } from "react";
import { useParams, useRouter } from "next/navigation";
import { checkoutApi, getImageUrl } from "@/lib/api";
import { motion, AnimatePresence } from "framer-motion";
import {
  ChevronLeft,
  Package,
  MapPin,
  CreditCard,
  Truck,
  ChevronDown,
  ChevronUp,
  Clock,
  CheckCircle,
  XCircle,
  HelpCircle
} from "lucide-react";
import Link from "next/link";

export default function OrderDetailPage() {
  const params = useParams();
  const router = useRouter();
  const [order, setOrder] = useState<any>(null);
  const [loading, setLoading] = useState(true);
  const [isItemsExpanded, setIsItemsExpanded] = useState(true);
  const [collapsedPackages, setCollapsedPackages] = useState<Record<string, boolean>>({});

  const togglePackage = (pkgNum: string) => {
    setCollapsedPackages((prev) => ({
      ...prev,
      [pkgNum]: !prev[pkgNum],
    }));
  };

  useEffect(() => {
    fetchOrderDetail();
  }, [params.id]);

  const fetchOrderDetail = async () => {
    try {
      const res = await checkoutApi.getOrderDetail(params.id as string);
      setOrder(res.data || res);
    } catch (e) {
      console.error("Failed to fetch order detail", e);
    } finally {
      setLoading(false);
    }
  };

  const getStatusDisplay = (status: string) => {
    switch (status) {
      case "pending":
        return { label: "Chờ tiếp nhận", color: "text-amber-600 bg-amber-50", icon: Clock };
      case "processing":
        return { label: "Chờ đóng gói", color: "text-blue-600 bg-blue-50", icon: Package };
      case "packed":
        return { label: "Đã đóng gói", color: "text-teal-600 bg-teal-50", icon: Package };
      case "shipping":
        return { label: "Đang giao hàng", color: "text-indigo-600 bg-indigo-50", icon: Truck };
      case "ready_for_pickup":
        return { label: "Sẵn sàng nhận", color: "text-cyan-600 bg-cyan-50", icon: MapPin };
      case "completed":
        return { label: "Đã hoàn tất", color: "text-green-600 bg-green-50", icon: CheckCircle };
      case "cancelled":
        return { label: "Đã hủy", color: "text-red-600 bg-red-50", icon: XCircle };
      default:
        return { label: status, color: "text-gray-600 bg-gray-50", icon: HelpCircle };
    }
  };

  if (loading) return <div className="h-96 bg-gray-50 animate-pulse rounded-3xl" />;
  if (!order) return <div className="p-10 text-center font-black text-[#002094]">Không tìm thấy đơn hàng</div>;

  const status = getStatusDisplay(order.status);
  const StatusIcon = status.icon;

  // Group items by package_number
  const packages = order.items?.reduce((groups: any, item: any) => {
    const pkgNum = item.package_number || `${order.order_number}FN1`;
    if (!groups[pkgNum]) groups[pkgNum] = [];
    groups[pkgNum].push(item);
    return groups;
  }, {});

  const renderStepper = () => {
    const status = order.status;
    const steps = [
      { key: "ordered", label: "Đơn đã đặt" },
      { key: "pending", label: "Chờ tiếp nhận" },
      { key: "processing", label: "Chờ đóng gói" },
      { key: "packed", label: "Đã đóng gói" },
      { key: "shipping", label: "Đang giao hàng" },
      { key: "completed", label: "Hoàn tất" }
    ];

    const statusHierarchy: Record<string, number> = {
      pending: 1,
      processing: 2,
      packed: 3,
      shipping: 4,
      completed: 5,
      failed: 5,
      cancelled: 0
    };

    const currentStepIndex = statusHierarchy[status] ?? 1;

    if (status === "cancelled") {
      return (
        <div className="flex items-center justify-center p-6 bg-red-50 border border-red-100 rounded-3xl gap-4 shadow-sm mb-8 w-full select-none text-red-700">
          <XCircle size={28} className="text-red-500 animate-bounce" />
          <div>
            <h3 className="font-extrabold text-[16px] text-red-800">Đơn hàng đã bị hủy</h3>
            <p className="text-xs text-red-500 font-semibold mt-0.5">Chúng tôi đã hủy đơn hàng theo yêu cầu hoặc do sự cố giao nhận.</p>
          </div>
        </div>
      );
    }

    const finalLabel = status === "failed" ? "Giao hàng thất bại" : "Giao thành công";

    return (
      <div className="flex items-center justify-between w-full p-6 bg-transparent border border-blue-200 rounded-2xl gap-2 mb-8 select-none overflow-x-auto">
        {steps.map((step, idx) => {
          let isActive = false;
          let isCompleted = false;

          if (step.key === "ordered") {
            isCompleted = true;
          } else if (step.key === "completed") {
            if (status === "completed" || status === "failed") {
              isActive = true;
              isCompleted = true;
            }
          } else {
            const stepIndex = statusHierarchy[step.key] ?? 0;
            if (currentStepIndex > stepIndex) {
              isCompleted = true;
            } else if (currentStepIndex === stepIndex) {
              isActive = true;
            }
          }

          const label = step.key === "completed" ? finalLabel : step.label;
          const isFailedStep = step.key === "completed" && status === "failed";
          const isSuccessStep = step.key === "completed" && status === "completed";

          const colorClass = isFailedStep
            ? "text-red-600 font-black"
            : isSuccessStep
              ? "text-green-600 font-black"
              : isCompleted
                ? "text-[#002094] font-black"
                : isActive
                  ? "text-blue-500 font-bold"
                  : "text-gray-300 font-medium";

          const bgClass = isFailedStep
            ? "bg-red-50 text-red-500 border-red-200"
            : isSuccessStep
              ? "bg-green-50 text-green-500 border-green-200"
              : isCompleted
                ? "bg-blue-50 text-[#002094] border-blue-200"
                : isActive
                  ? "bg-blue-50/50 text-blue-500 border-blue-200 animate-pulse"
                  : "bg-gray-50 text-gray-300 border-gray-100";

          return (
            <React.Fragment key={step.key}>
              {idx > 0 && (
                <div className={`flex-grow h-1 mx-2 rounded-full ${isCompleted ? "bg-[#002094]" : "bg-gray-100"} transition-all duration-500`} />
              )}
              <div className="flex flex-col items-center text-center gap-2 flex-shrink-0">
                <div className={`w-12 h-12 rounded-full border-2 ${bgClass} flex items-center justify-center shadow-sm transition-all duration-300`}>
                  {step.key === "ordered" && <Package size={22} />}
                  {step.key === "pending" && <Clock size={22} />}
                  {step.key === "processing" && <Package size={22} />}
                  {step.key === "packed" && <Package size={22} />}
                  {step.key === "shipping" && <Truck size={22} />}
                  {step.key === "completed" && (
                    status === "failed" ? <XCircle size={22} /> : <CheckCircle size={22} />
                  )}
                </div>
                <span className={`text-[12px] tracking-tight ${colorClass}`}>{label}</span>
              </div>
            </React.Fragment>
          );
        })}
      </div>
    );
  };

  return (
    <div className="space-y-6 pb-20 font-sans text-[#002094]">
      {/* Header */}
      <div className="space-y-1">
        <h2 className="text-[28px] font-bold">
          Đơn hàng #{order.order_number}
        </h2>
        <div className="flex items-center gap-2 text-[14px] text-gray-500 font-medium">
          <Clock size={14} />
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
        {packages && Object.entries(packages).map(([pkgNum, items]: any, idx) => {
          const isGiftPkg = pkgNum.includes('SXU') || pkgNum.includes('GIFT');
          const pkgTotal = items.reduce((sum: number, i: any) => sum + Number(i.total), 0);

          return (
            <div key={pkgNum} className="bg-transparent border border-blue-100 rounded-xl overflow-hidden shadow-sm">
              <div className="p-6 space-y-6">
                {/* Package Header */}
                <div
                  onClick={() => togglePackage(pkgNum)}
                  className="flex items-center justify-between cursor-pointer select-none hover:opacity-85 transition-opacity"
                >
                  <div className="flex items-center gap-3">
                    <h3 className="text-[18px] font-bold">Kiện hàng {idx + 1}</h3>
                    <span className={`px-2 py-0.5 rounded text-[11px] font-bold uppercase ${status.color}`}>
                      {status.label}
                    </span>
                  </div>
                  <div className="flex items-center gap-4">
                    <span className="text-[18px] font-bold">
                      {isGiftPkg ? '0đ' : `${pkgTotal.toLocaleString()}đ`}
                    </span>
                    <button className="p-1 hover:bg-[#002094]/5 rounded-full transition-colors">
                      {collapsedPackages[pkgNum] ? (
                        <ChevronDown size={20} className="text-[#002094]" />
                      ) : (
                        <ChevronUp size={20} className="text-[#002094]" />
                      )}
                    </button>
                  </div>
                </div>

                <div className="text-[14px]">
                  <p className="text-[#002094] font-black">Mã kiện hàng #{pkgNum}</p>
                </div>

                <AnimatePresence initial={false}>
                  {!collapsedPackages[pkgNum] && (
                    <motion.div
                      initial={{ height: 0, opacity: 0 }}
                      animate={{ height: "auto", opacity: 1 }}
                      exit={{ height: 0, opacity: 0 }}
                      transition={{ duration: 0.2 }}
                      className="overflow-hidden"
                    >
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
                                        `Thẻ quốc tế (${String(order.payment_method || '').toUpperCase()})`}
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
                              {order.delivery_type === 'pickup' ? (
                                <div className="space-y-2">
                                  <div className="p-3 bg-blue-50/50 border border-blue-100 rounded-lg">
                                    <p className="text-blue-900 font-black">{order.store?.name || 'Cửa hàng Vinamilk'}</p>
                                    <p className="text-xs text-gray-600 font-medium mt-0.5">
                                      {order.store?.address || 'Địa chỉ cửa hàng'}, {order.store?.ward || ''}, {order.store?.district || ''}, {order.store?.province || ''}
                                    </p>
                                  </div>
                                  <div className="text-xs space-y-1 text-blue-900 bg-blue-50/50 p-3 rounded-lg border border-blue-100">
                                    <p><b>Người nhận:</b> {order.shipping_address?.name || 'Chưa cập nhật'} • {order.shipping_address?.phone || '-'}</p>
                                    <p><b>Thời gian hẹn:</b> <span className="text-blue-900 font-bold">{order.pickup_time || '-'}</span></p>
                                  </div>
                                </div>
                              ) : (
                                <>
                                  <p>{order.shipping_address?.name || (order.shipping_address?.last_name + ' ' + order.shipping_address?.first_name)} • {order.shipping_address?.phone}</p>
                                  <p className="text-gray-500 font-medium">
                                    {order.shipping_address?.detail || order.shipping_address?.address}, {order.shipping_address?.ward}, {order.shipping_address?.district}, {order.shipping_address?.city}
                                  </p>
                                </>
                              )}
                            </div>
                          </div>

                          {order.invoice_info && (
                            <div className="space-y-1 pt-4 border-t border-gray-100">
                              <p className="text-[11px] font-bold text-red-500 uppercase tracking-wider">Thông tin xuất hóa đơn VAT</p>
                              <div className="text-[13px] bg-red-50/40 border border-red-100/60 p-4 rounded-lg space-y-1.5 mt-1 text-[#002060]">
                                <p><b>Loại hóa đơn:</b> <span className="px-2 py-0.5 bg-red-100 text-red-700 rounded text-xs font-black uppercase">{order.invoice_info.type === 'personal' ? 'Cá nhân' : 'Công ty / Đơn vị'}</span></p>
                                <p><b>{order.invoice_info.type === 'personal' ? 'Họ và tên:' : 'Tên công ty:'}</b> {order.invoice_info.name || '-'}</p>
                                <p><b>Mã số thuế:</b> {order.invoice_info.tax_code || '-'}</p>
                                <p><b>Địa chỉ:</b> {order.invoice_info.address || '-'}</p>
                                {order.invoice_info.phone && <p><b>Số điện thoại:</b> {order.invoice_info.phone}</p>}
                                {order.invoice_info.email && <p><b>Email nhận hóa đơn:</b> {order.invoice_info.email}</p>}
                              </div>
                            </div>
                          )}
                        </div>

                        {/* Products Section */}
                        <div className="space-y-4">
                          <h4 className="text-[11px] font-bold text-gray-400 uppercase tracking-wider">
                            {isGiftPkg ? 'QUÀ KÈM THEO ĐƠN HÀNG' : 'SẢN PHẨM'}
                          </h4>

                          <div className={`space-y-4 ${isGiftPkg ? 'bg-[#F9FFD9] p-4 rounded-lg' : ''}`}>
                            {items.map((item: any) => (
                              <div key={item.id} className="flex gap-4 items-center">
                                <div className="w-14 h-14 bg-white rounded flex-shrink-0 flex items-center justify-center p-1 border border-gray-100 relative">
                                  <img
                                    src={getImageUrl(item.image) || "/placeholder.png"}
                                    className="w-full h-full object-contain"
                                    alt={item.product_name}
                                  />
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
                                  {isGiftPkg ? (
                                    <span className="text-[14px] font-bold">Quà tặng</span>
                                  ) : (
                                    <>
                                      <p className="text-[15px] font-bold">{Number(item.total).toLocaleString()}đ</p>
                                      {item.original_price > item.price && (
                                        <p className="text-[11px] text-gray-300 line-through font-bold">
                                          {Number(item.original_price * item.quantity).toLocaleString()}đ
                                        </p>
                                      )}
                                    </>
                                  )}
                                </div>
                              </div>
                            ))}
                          </div>
                        </div>

                        {/* Individual Package Summary (Only if not gift) */}
                        {!isGiftPkg && (
                          <div className="space-y-2 pt-6 border-t border-gray-50">
                            <div className="flex justify-between text-[14px]">
                              <span className="font-medium text-gray-500">Tổng tiền hàng</span>
                              <span className="font-bold">{pkgTotal.toLocaleString()}đ</span>
                            </div>
                            <div className="flex justify-between text-[14px]">
                              <span className="font-medium text-gray-500">Phí vận chuyển</span>
                              <span className="font-bold">{idx === 0 ? Number(order.shipping_cost || 0).toLocaleString() : '0'}đ</span>
                            </div>
                            <div className="flex justify-between text-[14px] text-pink-500">
                              <span className="font-medium">Voucher giảm giá</span>
                              <span className="font-bold">-{Number(order.discount_amount || 0).toLocaleString()}đ</span>
                            </div>
                            <div className="flex justify-between text-[16px] pt-2 border-t border-gray-50">
                              <span className="font-bold">Tổng</span>
                              <span className="font-bold">{Number(order.total_amount || 0).toLocaleString()}đ</span>
                            </div>
                          </div>
                        )}

                        {isGiftPkg && (
                          <div className="space-y-2 pt-6 border-t border-gray-50">
                            <div className="flex justify-between text-[14px]">
                              <span className="font-medium text-gray-500">Tổng</span>
                              <span className="font-bold">0đ</span>
                            </div>
                          </div>
                        )}
                      </div>
                    </motion.div>
                  )}
                </AnimatePresence>
              </div>
            </div>
          );
        })}
      </div>

      {/* Re-order Button */}
      <button className="w-full bg-[#0000A0] text-white py-4 rounded-lg font-bold text-[16px] hover:bg-blue-800 transition-all">
        Mua lại
      </button>

      {/* Support */}
      <div className="text-center text-[13px] text-gray-400 font-medium">
        Bạn cần hỗ trợ? <Link href="/support" className="text-[#002094] font-bold">Liên hệ CSKH</Link>
      </div>
    </div>
  );
}


