import React, { forwardRef } from 'react';
import { ChevronDown } from 'lucide-react';

interface VSelectProps extends React.SelectHTMLAttributes<HTMLSelectElement> {
  label?: string;
  error?: string;
  options: { value: string; label: string }[];
}

const VSelect = forwardRef<HTMLSelectElement, VSelectProps>(
  ({ label, error, className = '', options, ...props }, ref) => {
    return (
      <div className="w-full mb-5 relative">
        {label && (
          <label className="block text-[#002094] text-[15px] font-medium mb-1 tracking-tight">
            {label}
          </label>
        )}
        <div className="relative">
          <select
            ref={ref}
            className={`w-full bg-[#EFEFEF] text-[#002094] text-[15px] px-4 py-3 appearance-none rounded-none border-b-2 border-[#002094] focus:outline-none focus:bg-white transition-colors disabled:opacity-50 disabled:cursor-not-allowed ${
              error ? 'border-red-500' : ''
            } ${className}`}
            {...props}
          >
            <option value="" disabled>Chọn {label?.replace(' *', '').toLowerCase()}</option>
            {options.map((opt) => (
              <option key={opt.value} value={opt.value}>
                {opt.label}
              </option>
            ))}
          </select>
          <div className="absolute right-4 top-1/2 -translate-y-1/2 pointer-events-none text-[#002094]">
            <ChevronDown size={20} strokeWidth={1.5} />
          </div>
        </div>
        {error && <p className="text-red-500 text-[12px] mt-1 font-medium">{error}</p>}
      </div>
    );
  }
);

VSelect.displayName = 'VSelect';

export default VSelect;
