export const getGradeColorClass = (grade) => {
  const gradeColors = {
    A: "bg-green-100 text-green-800",
    "A+": "bg-green-200 text-green-900",
    "A-": "bg-green-50 text-green-700",
    "B+": "bg-blue-200 text-blue-900",
    B: "bg-blue-100 text-blue-800",
    "B-": "bg-blue-50 text-blue-700",
    "C+": "bg-yellow-200 text-yellow-900",
    C: "bg-yellow-100 text-yellow-800",
    "C-": "bg-yellow-50 text-yellow-700",
    "D+": "bg-orange-200 text-orange-900",
    D: "bg-orange-100 text-orange-800",
    "D-": "bg-orange-50 text-orange-700",
    E: "bg-red-200 text-red-900",
  };
  return gradeColors[grade] || "bg-gray-100 text-gray-800";
};

export const getTotalColorClass = (total) => {
  if (total >= 80) return "text-green-600";
  if (total >= 70) return "text-blue-600";
  if (total >= 60) return "text-yellow-600";
  if (total >= 50) return "text-orange-600";
  if (total === 0) return "text-gray-600";
  return "text-red-600";
};

export const calculateGrade = (totalMark) => {
  if (totalMark >= 90) return "A+";
  if (totalMark >= 80) return "A";
  if (totalMark >= 75) return "A-";
  if (totalMark >= 70) return "B+";
  if (totalMark >= 65) return "B";
  if (totalMark >= 60) return "B-";
  if (totalMark >= 55) return "C+";
  if (totalMark >= 50) return "C";
  if (totalMark >= 45) return "C-";
  if (totalMark >= 40) return "D+";
  if (totalMark >= 35) return "D";
  if (totalMark >= 30) return "D-";
  return "E";
};
