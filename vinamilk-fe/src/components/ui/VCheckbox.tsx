import React from 'react';
import { Check } from 'lucide-react';

interface VCheckboxProps extends React.InputHTMLAttributes<HTMLInputElement> {
  label: React.ReactNode;
}

const VCheckbox: React.FC<VCheckboxProps> = ({ label, className = '', checked, onChange, ...props }) => {
  return (
    <label className={`flex items-start cursor-pointer group ${className}`}>
      <div className="relative flex items-center justify-center w-5 h-5 mt-0.5 mr-3 flex-shrink-0">
        <input 
          type="checkbox" 
          className="peer appearance-none w-5 h-5 border border-[#002094] rounded-sm bg-transparent checked:bg-transparent transition-all cursor-pointer"
          checked={checked}
          onChange={onChange}
          {...props}
        />
        <Check 
          size={14} 
          strokeWidth={3}
          className="absolute text-[#002094] opacity-0 peer-checked:opacity-100 pointer-events-none transition-opacity" 
        />
      </div>
      <span className="text-[#002094] text-[13px] md:text-[14px] font-medium leading-tight">
        {label}
      </span>
    </label>
  );
};

export default VCheckbox;
