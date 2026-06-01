import React, { forwardRef, useState } from 'react';
import { Eye, EyeOff } from 'lucide-react';

interface VInputProps extends React.InputHTMLAttributes<HTMLInputElement> {
  label?: string;
  error?: string;
}

const VInput = forwardRef<HTMLInputElement, VInputProps>(
  ({ label, error, type = 'text', className = '', ...props }, ref) => {
    const [showPassword, setShowPassword] = useState(false);
    const isPassword = type === 'password';

    return (
      <div className="w-full mb-5">
        {label && (
          <label className="block text-[#002094] text-[15px] font-medium mb-1 tracking-tight">
            {label}
          </label>
        )}
        <div className="relative">
          <input
            ref={ref}
            type={isPassword && showPassword ? 'text' : type}
            className={`w-full bg-[#EFEFEF] text-[#002094] text-[15px] px-4 py-3 rounded-none border-b-2 border-[#002094] focus:outline-none focus:bg-white transition-colors placeholder:text-[#002094]/50 ${
              error ? 'border-red-500' : ''
            } ${className}`}
            {...props}
          />
          {isPassword && (
            <button
              type="button"
              onClick={() => setShowPassword(!showPassword)}
              className="absolute right-4 top-1/2 -translate-y-1/2 text-[#002094] hover:opacity-70 transition-opacity"
            >
              {showPassword ? <EyeOff size={20} /> : <Eye size={20} />}
            </button>
          )}
        </div>
        {error && <p className="text-red-500 text-[12px] mt-1 font-medium">{error}</p>}
      </div>
    );
  }
);

VInput.displayName = 'VInput';

export default VInput;
