import React from 'react';

interface VButtonProps extends React.ButtonHTMLAttributes<HTMLButtonElement> {
  variant?: 'primary' | 'outline' | 'ghost';
  isLoading?: boolean;
}

const VButton: React.FC<VButtonProps> = ({ 
  children, 
  className = '', 
  variant = 'primary',
  isLoading = false,
  disabled,
  ...props 
}) => {
  const baseStyle = "w-full py-3 px-6 rounded-md font-bold text-[15px] transition-all flex items-center justify-center tracking-tight disabled:opacity-50 disabled:cursor-not-allowed";
  
  const variants = {
    primary: "bg-[#002094] text-white hover:bg-[#001870] shadow-sm",
    outline: "bg-transparent border-2 border-[#002094] text-[#002094] hover:bg-[#002094]/5",
    ghost: "bg-transparent text-[#002094] hover:bg-[#002094]/5"
  };

  return (
    <button 
      className={`${baseStyle} ${variants[variant]} ${className}`}
      disabled={disabled || isLoading}
      {...props}
    >
      {isLoading ? (
        <div className="w-5 h-5 border-2 border-white/30 border-t-white rounded-full animate-spin mr-2" />
      ) : null}
      {children}
    </button>
  );
};

export default VButton;
