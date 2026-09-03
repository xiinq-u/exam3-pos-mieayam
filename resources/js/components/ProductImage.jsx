import { useEffect, useState } from "react";
import { getProductImageUrl } from "../services/imagePreloader";

function ProductImage({
    image,
    alt,
    index = Number.MAX_SAFE_INTEGER,
    className = "h-full w-full object-cover",
    fallbackClassName = "flex h-full w-full items-center justify-center text-[10px] font-black tracking-widest text-stone-300 uppercase",
}) {
    const imageUrl = getProductImageUrl(image);
    const [failed, setFailed] = useState(false);

    useEffect(() => {
        setFailed(false);
    }, [imageUrl]);

    if (!imageUrl || failed) {
        return <div className={fallbackClassName}>No Image</div>;
    }

    return (
        <img
            src={imageUrl}
            alt={alt}
            loading="eager"
            decoding="async"
            fetchPriority={index < 6 ? "high" : "auto"}
            onError={() => setFailed(true)}
            className={className}
        />
    );
}

export default ProductImage;
